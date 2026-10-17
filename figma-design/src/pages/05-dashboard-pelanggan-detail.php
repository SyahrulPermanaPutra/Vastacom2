<?php
$title = 'Dashboard Pelanggan — Modal Detail Service';
$desc  = 'Detail service, foto kerusakan, riwayat status (timeline) & invoice.';
$tl = function ($status, $by, $time, $note, $last = false) use ($STATUS_LABEL) {
    return '<div class="tl-item"><div class="tl-rail"><div class="tl-dot"></div>' . ($last ? '' : '<div class="tl-line"></div>') . '</div>'
        . '<div class="tl-body"><div class="row between items-center">' . badge($status, $STATUS_LABEL[$status]) . '<div class="small muted">' . $time . '</div></div>'
        . '<div class="small muted">Diupdate oleh: ' . $by . '</div>' . ($note ? '<div class="note">' . $note . '</div>' : '') . '</div></div>';
};
$body = '<div class="row gap-24 items-start">'
  . '<div class="col gap-16 grow">'
  .   '<div class="h4">Informasi Service</div>'
  .   kv(['ID Service' => '#SVC-125', 'Pelanggan' => 'Budi Santoso', 'Tanggal Masuk' => '12 Okt 2026 09:15', 'Tanggal Update' => '14 Okt 2026 15:02', 'Teknisi' => 'teknisi_andi', 'Status' => badge('diproses', 'Diproses')])
  .   '<div class="h4">Detail Kerusakan</div>'
  .   '<div class="col gap-4"><div class="small semibold">Jenis Kerusakan</div><div class="body">Laptop Tidak Nyala</div></div>'
  .   '<div class="col gap-4"><div class="small semibold">Deskripsi</div><div class="body">Laptop mati total setelah terkena air, indikator charger tidak menyala.</div></div>'
  .   '<div class="col gap-8"><div class="small semibold">Foto Kerusakan</div><div class="photo">' . ic('image') . '<span>foto_kerusakan.jpg</span></div></div>'
  . '</div>'
  . '<div class="col gap-16 grow">'
  .   '<div class="h4">Riwayat Status</div>'
  .   '<div class="timeline">' . $tl('diproses', 'teknisi_andi', '14 Okt 2026 15:02', 'Motherboard dibersihkan, menunggu pengeringan komponen.') . $tl('menunggu', 'admin', '12 Okt 2026 09:15', 'Perangkat diterima di toko.', true) . '</div>'
  .   '<div class="h4">Informasi Invoice</div>'
  .   kv(['No. Invoice' => 'INV-31', 'Tanggal Invoice' => '14 Okt 2026 15:10', 'Total Biaya' => '<span class="bold">Rp750.000</span>', 'Status Pembayaran' => badge('menunggu', 'Pending'), 'Metode Pembayaran' => 'Transfer BCA', 'Keterangan' => 'Pembersihan board + ganti IC power'])
  . '</div></div>';
?>
<div class="frame">
  <?= overlay(modal('Detail Service', $body, btn('Tutup', 'secondary'), 'modal-lg')) ?>
</div>
