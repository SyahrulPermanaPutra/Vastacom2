<?php
// Query & komponen service yang dipakai bersama oleh dashboard pelanggan dan panel admin/teknisi.

const SERVICE_SELECT = "SELECT s.*, p.nama AS nama_pelanggan, u.username AS nama_teknisi
                        FROM service s
                        JOIN pelanggan p ON s.id_pelanggan = p.id
                        LEFT JOIN users u ON s.id_teknisi = u.id";

function service_find($conn, $id) {
    return db_one($conn, SERVICE_SELECT . " WHERE s.id = ?", 'i', $id);
}

function service_history($conn, $id) {
    return db_all($conn, "SELECT h.*, u.username AS oleh FROM history_status h LEFT JOIN users u ON h.updated_by = u.id
                          WHERE h.service_id = ? ORDER BY h.updated_on DESC, h.id DESC", 'i', $id);
}

function service_invoice($conn, $id) {
    return db_one($conn, "SELECT * FROM invoice WHERE service_id = ? ORDER BY id DESC LIMIT 1", 'i', $id);
}

function add_status_history($conn, $serviceId, $status, $userId, $note = '') {
    return db_exec($conn, "INSERT INTO history_status (service_id, status, updated_on, updated_by, catatan) VALUES (?, ?, NOW(), ?, ?)",
        'isis', $serviceId, $status, $userId, $note);
}

/** Service berstatus selesai yang belum memiliki invoice (bisa dibuatkan invoice). */
function services_without_invoice($conn) {
    return db_all($conn, SERVICE_SELECT . " WHERE s.status = 'selesai' AND NOT EXISTS (SELECT 1 FROM invoice i WHERE i.service_id = s.id) ORDER BY s.tanggal_masuk DESC");
}

/** Validasi & simpan invoice baru. Mengembalikan pesan error atau null jika berhasil. */
function create_invoice($conn, $post, $adminId) {
    $serviceId = (int)($post['service_id'] ?? 0);
    $total     = (float)($post['total_biaya'] ?? 0);
    $status    = $post['status_pembayaran'] ?? 'pending';
    if (!isset(STATUS_BAYAR[$status])) return 'Status pembayaran tidak valid.';
    if (!in_array($post['metode_pembayaran'] ?? '', METODE_BAYAR, true)) return 'Pilih metode pembayaran.';
    if ($total <= 0) return 'Total biaya harus lebih dari 0.';
    $service = db_one($conn, "SELECT status FROM service WHERE id = ?", 'i', $serviceId);
    if (!$service) return 'Servis tidak ditemukan.';
    if ($service['status'] !== 'selesai') return 'Invoice hanya bisa dibuat untuk servis yang sudah selesai.';
    if (service_invoice($conn, $serviceId)) return 'Servis ini sudah memiliki invoice.';

    $now   = date('Y-m-d H:i:s');
    $bayar = $status === 'lunas' ? $now : null;
    db_exec($conn, "INSERT INTO invoice (service_id, tanggal_invoice, total_biaya, keterangan, metode_pembayaran, status_pembayaran, tanggal_pembayaran, id_admin_pembuat)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 'isdssssi',
        $serviceId, $now, $total, trim($post['keterangan'] ?? ''), trim($post['metode_pembayaran'] ?? ''), $status, $bayar, $adminId);
    return null;
}

/** Isi modal "Detail Service" (Figma: 05 & 18). */
function service_detail_body($conn, $s, $invoiceActions = '') {
    $history = service_history($conn, $s['id']);
    $invoice = service_invoice($conn, $s['id']);
    $kv = function ($pairs) {
        $h = '<div class="table table-bordered">';
        foreach ($pairs as $k => $v) $h .= '<div class="kv"><div class="kv-key">' . $k . '</div><div class="kv-val">' . $v . '</div></div>';
        return $h . '</div>';
    };
    $foto = service_photo_url($s['foto_kerusakan']);

    $left = '<div class="h4">Informasi Servis</div>'
        . $kv([
            'ID Servis'      => '#SVC-' . (int)$s['id'],
            'Pelanggan'      => e($s['nama_pelanggan']),
            'Tanggal Masuk'  => tgl($s['tanggal_masuk']),
            'Terakhir Diubah' => tgl($s['tanggal_update']),
            'Teknisi'        => e($s['nama_teknisi'] ?: '-'),
            'Status'         => badge_service($s['status']),
        ])
        . '<div class="h4">Detail Kerusakan</div>'
        . '<div class="col gap-4"><div class="small semibold">Jenis Kerusakan</div><div class="body">' . e($s['jenis_kerusakan']) . '</div></div>'
        . '<div class="col gap-4"><div class="small semibold">Deskripsi</div><div class="body">' . nl2br(e($s['deskripsi'])) . '</div></div>'
        . '<div class="col gap-8"><div class="small semibold">Foto Kerusakan</div>'
        . ($foto ? '<a href="' . e($foto) . '" target="_blank"><img class="photo-img" src="' . e($foto) . '" alt="Foto kerusakan"></a>' : '<div class="photo">' . ic('image') . '<span>Tidak ada foto</span></div>')
        . '</div>';

    $tl = '';
    foreach ($history as $i => $h) {
        $last = $i === count($history) - 1;
        $tl .= '<div class="tl-item"><div class="tl-rail"><div class="tl-dot"></div>' . ($last ? '' : '<div class="tl-line"></div>') . '</div>'
            . '<div class="tl-body"><div class="row between items-center gap-8">' . badge_service($h['status']) . '<div class="small muted">' . tgl($h['updated_on']) . '</div></div>'
            . '<div class="small muted">Diupdate oleh: ' . e($h['oleh'] ?: 'System') . '</div>'
            . ($h['catatan'] !== null && $h['catatan'] !== '' ? '<div class="note">' . nl2br(e($h['catatan'])) . '</div>' : '') . '</div></div>';
    }
    $right = '<div class="h4">Riwayat Status</div>'
        . ($tl ? '<div class="timeline">' . $tl . '</div>' : '<div class="alert alert-info">' . ic('alert') . '<span>Belum ada riwayat status.</span></div>')
        . '<div class="row between items-center"><div class="h4">Informasi Invoice</div>' . ($invoice ? $invoiceActions : '') . '</div>'
        . ($invoice ? $kv([
            'No. Invoice'       => 'INV-' . (int)$invoice['id'],
            'Tanggal Invoice'   => tgl($invoice['tanggal_invoice']),
            'Total Biaya'       => '<span class="bold">' . rp($invoice['total_biaya']) . '</span>',
            'Status Pembayaran' => badge_bayar($invoice['status_pembayaran']),
            'Metode Pembayaran' => e($invoice['metode_pembayaran'] ?: '-'),
            'Tanggal Bayar'     => tgl($invoice['tanggal_pembayaran']),
            'Keterangan'        => nl2br(e($invoice['keterangan'] ?: '-')),
        ]) : '<div class="alert alert-info">' . ic('receipt') . '<span>Invoice belum dibuat.</span></div>');

    return '<div class="row gap-24 items-start stack"><div class="col gap-16 grow" style="min-width:0">' . $left . '</div><div class="col gap-16 grow" style="min-width:0">' . $right . '</div></div>';
}
