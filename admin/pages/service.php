<?php
// Service Management (Figma: 16, 17, 18 & 19)
// Admin: kelola semua service. Teknisi: hanya service yang ditugaskan kepadanya (update status & catatan).
$isAdmin  = $me['role'] === 'admin';
$photoDir = ROOT_PATH . '/uploads';

/** Service yang boleh diakses user ini, atau null. */
$findAllowed = function ($id) use ($conn, $me, $isAdmin) {
    $s = service_find($conn, (int)$id);
    return ($s && ($isAdmin || (int)$s['id_teknisi'] === (int)$me['id'])) ? $s : null;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $back   = 'admin/index.php' . qs();

    if ($action === 'tambah' && $isAdmin) {
        $pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
        $jenis     = trim($_POST['jenis_kerusakan'] ?? '');
        $teknisi   = (int)($_POST['id_teknisi'] ?? 0) ?: null;
        $masuk     = date('Y-m-d H:i:s', strtotime($_POST['tanggal_masuk'] ?? 'now') ?: time());
        $foto      = upload_image('foto_kerusakan', $photoDir);
        if (!$pelanggan || $jenis === '') {
            flash('danger', 'Pelanggan dan jenis kerusakan wajib diisi.');
        } elseif ($foto === false) {
            flash('danger', 'Foto gagal diunggah. Gunakan file JPG, JPEG, PNG, GIF, atau WEBP.');
        } else {
            db_exec($conn, "INSERT INTO service (id_pelanggan, id_teknisi, jenis_kerusakan, deskripsi, status, tanggal_masuk, tanggal_update, foto_kerusakan)
                            VALUES (?, ?, ?, ?, 'menunggu', ?, NOW(), ?)", 'iissss',
                $pelanggan, $teknisi, $jenis, trim($_POST['deskripsi'] ?? ''), $masuk, $foto ?: null);
            add_status_history($conn, $conn->insert_id, 'menunggu', $me['id'], 'Perangkat diterima di toko.');
            flash('success', 'Data servis berhasil <b>ditambahkan</b>.');
        }
        redirect($back);
    }

    $s = $findAllowed($_POST['id'] ?? 0);
    if (!$s) {
        flash('danger', 'Servis tidak ditemukan atau bukan tugas Anda.');
        redirect($back);
    }

    if ($action === 'edit') {
        $status  = $_POST['status'] ?? $s['status'];
        $catatan = trim($_POST['catatan'] ?? '');
        $foto    = upload_image('foto_kerusakan', $photoDir);
        if (!isset(STATUS_SERVICE[$status])) {
            flash('danger', 'Status tidak valid.');
        } elseif ($foto === false) {
            flash('danger', 'Foto gagal diunggah. Gunakan file JPG, JPEG, PNG, GIF, atau WEBP.');
        } else {
            if ($foto) delete_file($photoDir, $s['foto_kerusakan']);
            $foto = $foto ?: $s['foto_kerusakan'];
            if ($isAdmin) {
                $masuk = date('Y-m-d H:i:s', strtotime($_POST['tanggal_masuk'] ?? '') ?: strtotime($s['tanggal_masuk']));
                db_exec($conn, "UPDATE service SET jenis_kerusakan = ?, deskripsi = ?, status = ?, tanggal_masuk = ?, id_teknisi = ?, foto_kerusakan = ?, tanggal_update = NOW() WHERE id = ?",
                    'ssssisi', trim($_POST['jenis_kerusakan'] ?? $s['jenis_kerusakan']), trim($_POST['deskripsi'] ?? ''), $status, $masuk,
                    ((int)($_POST['id_teknisi'] ?? 0) ?: null), $foto, $s['id']);
            } else {
                db_exec($conn, "UPDATE service SET status = ?, foto_kerusakan = ?, tanggal_update = NOW() WHERE id = ?", 'ssi', $status, $foto, $s['id']);
            }
            // Riwayat dicatat saat status berubah atau ada catatan progres baru
            if ($status !== $s['status'] || $catatan !== '') {
                add_status_history($conn, $s['id'], $status, $me['id'], $catatan);
            }
            flash('success', 'Data servis berhasil <b>diperbarui</b>.');
        }
    } elseif ($action === 'hapus' && $isAdmin) {
        if (service_invoice($conn, $s['id'])) {
            flash('danger', 'Servis ini sudah memiliki invoice. Hapus invoice-nya terlebih dahulu di menu Invoice.');
        } else {
            $conn->begin_transaction();
            db_exec($conn, "DELETE FROM history_status WHERE service_id = ?", 'i', $s['id']);
            db_exec($conn, "DELETE FROM notifikasi WHERE service_id = ?", 'i', $s['id']);
            db_exec($conn, "DELETE FROM service WHERE id = ?", 'i', $s['id']);
            $conn->commit();
            delete_file($photoDir, $s['foto_kerusakan']);
            flash('success', 'Data servis berhasil <b>dihapus</b>.');
        }
    } elseif ($action === 'invoice' && $isAdmin) {
        $_POST['service_id'] = $s['id'];
        $error = create_invoice($conn, $_POST, $me['id']);
        $error ? flash('danger', $error) : flash('success', 'Invoice untuk <b>#SVC-' . (int)$s['id'] . '</b> berhasil dibuat.');
    }
    redirect($back);
}

// ---- Data tabel ----
$q      = trim($_GET['q'] ?? '');
$status = isset(STATUS_SERVICE[$_GET['status'] ?? '']) ? $_GET['status'] : '';
$like   = '%' . $q . '%';
$where  = " WHERE (s.jenis_kerusakan LIKE ? OR s.deskripsi LIKE ? OR p.nama LIKE ?)"
        . ($status ? " AND s.status = '" . $status . "'" : '')
        . ($isAdmin ? '' : ' AND s.id_teknisi = ' . (int)$me['id']);
$total  = db_one($conn, "SELECT COUNT(*) n FROM service s JOIN pelanggan p ON s.id_pelanggan = p.id" . $where, 'sss', $like, $like, $like)['n'];
[$limit, $pageNo, $offset, $pages] = paginate($total);
$rows = db_all($conn, SERVICE_SELECT . $where . " ORDER BY s.tanggal_masuk DESC LIMIT $limit OFFSET $offset", 'sss', $like, $like, $like);

$invoiced = [];
foreach (db_all($conn, "SELECT service_id, id, status_pembayaran FROM invoice") as $r) $invoiced[(int)$r['service_id']] = $r;

// Service yang diminta lewat ?view=ID (mis. dari dashboard) ikut dirender agar modalnya bisa langsung dibuka
$viewId = (int)($_GET['view'] ?? 0);
$modalRows = $rows;
if ($viewId && !in_array($viewId, array_map('intval', array_column($rows, 'id')), true) && ($v = $findAllowed($viewId))) {
    $modalRows[] = $v;
}

if ($isAdmin) {
    $pelangganList = db_all($conn, "SELECT id, nama FROM pelanggan ORDER BY nama");
}
$teknisiList = db_all($conn, "SELECT id, username FROM users WHERE role = 'teknisi' ORDER BY username");

$teknisiSelect = function ($selected = null) use ($teknisiList) {
    $h = '<select class="input" name="id_teknisi"><option value="">-- Pilih Teknisi --</option>';
    foreach ($teknisiList as $t) $h .= '<option value="' . (int)$t['id'] . '"' . ((int)$selected === (int)$t['id'] ? ' selected' : '') . '>' . e($t['username']) . '</option>';
    return $h . '</select>';
};
$photoField = '<label class="field"><span class="label">Foto Kerusakan</span><input class="input input-file" type="file" name="foto_kerusakan" accept=".jpg,.jpeg,.png,.gif,.webp"><span class="help">Format: JPG, JPEG, PNG, GIF, WEBP</span></label>';

$editForm = function ($s) use ($isAdmin, $teknisiSelect, $photoField) {
    $foto = service_photo_url($s['foto_kerusakan']);
    $statusOpt = '';
    foreach (STATUS_SERVICE as $k => [$label]) $statusOpt .= '<option value="' . $k . '"' . ($s['status'] === $k ? ' selected' : '') . '>' . $label . '</option>';
    $h = '<form method="post" enctype="multipart/form-data">' . csrf_field()
        . '<input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="' . (int)$s['id'] . '"><div class="modal-body">'
        . '<label class="field"><span class="label">Pelanggan</span><input class="input" value="' . e($s['nama_pelanggan']) . '" readonly></label>';
    if ($isAdmin) {
        $h .= '<label class="field"><span class="label">Jenis Kerusakan</span><input class="input" name="jenis_kerusakan" value="' . e($s['jenis_kerusakan']) . '" required></label>'
            . '<label class="field"><span class="label">Deskripsi</span><textarea class="input" name="deskripsi" rows="3">' . e($s['deskripsi']) . '</textarea></label>'
            . '<div class="field-row"><label class="field"><span class="label">Status</span><select class="input" name="status">' . $statusOpt . '</select></label>'
            . '<label class="field"><span class="label">Tanggal Masuk</span><input class="input" type="datetime-local" name="tanggal_masuk" value="' . date('Y-m-d\TH:i', strtotime($s['tanggal_masuk'])) . '" required></label></div>'
            . '<label class="field"><span class="label">Teknisi</span>' . $teknisiSelect($s['id_teknisi']) . '</label>';
    } else {
        $h .= '<label class="field"><span class="label">Jenis Kerusakan</span><input class="input" value="' . e($s['jenis_kerusakan']) . '" readonly></label>'
            . '<label class="field"><span class="label">Status</span><select class="input" name="status">' . $statusOpt . '</select></label>';
    }
    $h .= '<label class="field"><span class="label">Catatan Perubahan</span><textarea class="input" name="catatan" rows="2" placeholder="Contoh: Motherboard dibersihkan, menunggu pengeringan komponen."></textarea>'
        . '<span class="help">Disimpan ke riwayat status yang dilihat pelanggan.</span></label>'
        . $photoField
        . ($foto ? '<div class="row gap-12 items-center"><div class="thumb" style="width:120px;height:84px"><img class="thumb-img" src="' . e($foto) . '" alt=""></div><div class="small muted">Foto saat ini</div></div>' : '')
        . '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Tutup</button><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div></form>';
    return $h;
};
?>
<?= page_head('Manajemen Servis', $isAdmin ? 'Kelola data perbaikan perangkat pelanggan.' : 'Daftar servis yang ditugaskan kepada Anda. Perbarui status & catatan progres pengerjaan.') ?>
<?= render_flash() ?>

<div class="card">
  <form method="get" class="card-body toolbar">
    <input type="hidden" name="page" value="service">
    <?php if ($isAdmin): ?>
      <button type="button" class="btn btn-primary" data-open="modalTambah"><?= ic('plus', 'icon icon-sm') ?><span>Tambah Servis</span></button>
    <?php else: ?><div></div><?php endif; ?>
    <div class="row gap-12">
      <select class="input input-sm" name="status" data-autosubmit>
        <option value="">Semua Status</option>
        <?php foreach (STATUS_SERVICE as $k => [$label]): ?><option value="<?= $k ?>"<?= $status === $k ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
      </select>
      <div class="input-group" style="width:440px">
        <input class="input" name="q" value="<?= e($q) ?>" placeholder="Cari jenis kerusakan, deskripsi, atau pelanggan...">
        <button class="btn btn-outline-secondary" type="submit"><?= ic('search', 'icon icon-sm') ?><span>Cari</span></button>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <?php if (!$rows): ?>
    <div class="empty"><?= ic('wrench', 'icon icon-xl') ?><?= $isAdmin ? 'Tidak ada data servis.' : 'Belum ada servis yang ditugaskan kepada Anda.' ?></div>
  <?php else: ?>
  <div class="table-wrap"><div class="table" style="min-width:1280px">
    <div class="tr thead">
      <div class="th" style="width:56px">No</div><div class="th" style="width:160px">Pelanggan</div><div class="th" style="width:170px">Jenis Kerusakan</div><div class="th" style="flex:1">Deskripsi</div>
      <div class="th" style="width:140px">Status</div><div class="th" style="width:170px">Tanggal Masuk</div><div class="th" style="width:140px">Teknisi</div><div class="th" style="width:170px">Invoice</div><div class="th" style="width:<?= $isAdmin ? 130 : 100 ?>px">Aksi</div>
    </div>
    <?php foreach ($rows as $i => $s): $sid = (int)$s['id']; ?>
    <div class="tr">
      <div class="td" style="width:56px"><?= $offset + $i + 1 ?></div>
      <div class="td semibold" style="width:160px"><?= e($s['nama_pelanggan']) ?></div>
      <div class="td" style="width:170px"><?= e($s['jenis_kerusakan']) ?></div>
      <div class="td" style="flex:1;min-width:0"><span class="muted clamp"><?= e($s['deskripsi']) ?></span></div>
      <div class="td" style="width:140px"><?= badge_service($s['status']) ?></div>
      <div class="td" style="width:170px"><?= tgl($s['tanggal_masuk']) ?></div>
      <div class="td" style="width:140px"><?= e($s['nama_teknisi'] ?: '-') ?></div>
      <div class="td" style="width:170px">
        <?php if (isset($invoiced[$sid])): $inv = $invoiced[$sid]; ?>
          <div class="col gap-4">
            <?php if ($isAdmin): ?><a class="link small" target="_blank" title="Cetak invoice" href="<?= e(url('admin/index.php?page=invoice_cetak&id=' . (int)$inv['id'])) ?>">INV-<?= (int)$inv['id'] ?></a><?php else: ?><span class="small semibold">INV-<?= (int)$inv['id'] ?></span><?php endif; ?>
            <?= badge_bayar($inv['status_pembayaran']) ?>
          </div>
        <?php elseif ($s['status'] === 'selesai' && $isAdmin): ?>
          <button type="button" class="btn btn-success btn-sm" data-open="invoice-<?= $sid ?>"><?= ic('receipt', 'icon icon-sm') ?><span>Buat Invoice</span></button>
        <?php elseif ($s['status'] === 'selesai'): ?>
          <span class="small muted">Belum dibuat</span>
        <?php else: ?>
          <span class="small muted">Menunggu servis selesai</span>
        <?php endif; ?>
      </div>
      <div class="td" style="width:<?= $isAdmin ? 130 : 100 ?>px;gap:6px">
        <button type="button" class="btn btn-warning btn-sm btn-icon" title="Ubah" data-open="edit-<?= $sid ?>"><?= ic('edit', 'icon icon-sm') ?></button>
        <?php if ($isAdmin): ?><?= delete_button('hapus', $sid, 'Servis #SVC-' . $sid . ' — ' . $s['jenis_kerusakan'] . ' milik ' . $s['nama_pelanggan'] . ' akan dihapus permanen beserta riwayat dan fotonya.', 'Hapus', true) ?><?php endif; ?>
        <button type="button" class="btn btn-info btn-sm btn-icon" title="Detail" data-open="detail-<?= $sid ?>"><?= ic('eye', 'icon icon-sm') ?></button>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>

  <div class="card-body toolbar" style="border-top:1px solid var(--border)">
    <div class="row gap-16 small muted items-center" style="flex-wrap:wrap">
      <span>Menampilkan <?= $total ? ($offset + 1) . '–' . ($offset + count($rows)) : 0 ?> dari <?= (int)$total ?> servis</span>
      <span class="row items-center gap-4"><?= ic('edit', 'icon icon-sm') ?> Ubah</span>
      <?php if ($isAdmin): ?><span class="row items-center gap-4"><?= ic('trash', 'icon icon-sm') ?> Hapus</span><?php endif; ?>
      <span class="row items-center gap-4"><?= ic('eye', 'icon icon-sm') ?> Detail</span>
    </div>
    <?= pagination_links($pageNo, $pages) ?>
  </div>
</div>

<?php if ($isAdmin): ?>
<?= modal_open('modalTambah', 'Tambah Servis Baru') ?>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="tambah">
  <div class="modal-body">
    <label class="field"><span class="label">Pelanggan</span>
      <select class="input" name="id_pelanggan" required><option value="">-- Pilih Pelanggan --</option>
        <?php foreach ($pelangganList as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?>
      </select>
      <span class="help">Pelanggan belum terdaftar? Tambahkan dulu di menu <a class="link" href="<?= e(url('admin/index.php?page=pengguna&role=pelanggan')) ?>">Pengguna</a>.</span></label>
    <label class="field"><span class="label">Jenis Kerusakan</span><input class="input" name="jenis_kerusakan" placeholder="Contoh: Laptop tidak nyala" required></label>
    <label class="field"><span class="label">Deskripsi</span><textarea class="input" name="deskripsi" rows="3" placeholder="Jelaskan kerusakan secara detail..."></textarea></label>
    <div class="field-row">
      <label class="field"><span class="label">Tanggal Masuk</span><input class="input" type="datetime-local" name="tanggal_masuk" value="<?= date('Y-m-d\TH:i') ?>" required></label>
      <label class="field"><span class="label">Teknisi</span><?= $teknisiSelect() ?></label>
    </div>
    <?= $photoField ?>
    <div class="alert alert-info"><?= ic('clock') ?><span>Servis baru otomatis berstatus <b>Menunggu</b>.</span></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Tutup</button><button type="submit" class="btn btn-primary">Simpan Servis</button></div>
</form>
<?= modal_close() ?>
<?php endif; ?>

<?php foreach ($modalRows as $s): $sid = (int)$s['id']; ?>
  <?= modal_open('edit-' . $sid, 'Ubah Servis #SVC-' . $sid) . $editForm($s) . modal_close() ?>

  <?= str_replace('data-modal>', 'data-modal' . ($sid === $viewId ? ' data-autoopen' : '') . '>', modal_open('detail-' . $sid, 'Detail Servis', 'modal-lg')) ?>
  <div class="modal-body"><?= service_detail_body($conn, $s, $isAdmin && isset($invoiced[$sid]) ? '<a class="btn btn-outline-secondary btn-sm" target="_blank" href="' . e(url('admin/index.php?page=invoice_cetak&id=' . (int)$invoiced[$sid]['id'])) . '">' . ic('printer', 'icon icon-sm') . '<span>Cetak</span></a>' : '') ?></div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Tutup</button></div>
  <?= modal_close() ?>

  <?php if ($isAdmin && $s['status'] === 'selesai' && !isset($invoiced[$sid])): ?>
  <?= modal_open('invoice-' . $sid, 'Buat Invoice') ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="invoice"><input type="hidden" name="id" value="<?= $sid ?>">
    <div class="modal-body">
      <div class="alert alert-info"><?= ic('wrench') ?><span>Servis <b>#SVC-<?= $sid ?></b> — <?= e($s['nama_pelanggan']) ?> · <?= e($s['jenis_kerusakan']) ?></span></div>
      <label class="field"><span class="label">Total Biaya (Rp)</span><input class="input" type="number" name="total_biaya" min="1" step="1000" required></label>
      <label class="field"><span class="label">Metode Pembayaran</span><select class="input" name="metode_pembayaran" required><?= metode_options() ?></select></label>
      <label class="field"><span class="label">Status Pembayaran</span><select class="input" name="status_pembayaran">
        <?php foreach (STATUS_BAYAR as $k => [$label]): ?><option value="<?= $k ?>"><?= $label ?></option><?php endforeach; ?></select></label>
      <label class="field"><span class="label">Keterangan</span><textarea class="input" name="keterangan" rows="3" placeholder="Catatan tambahan (opsional)"></textarea></label>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button><button type="submit" class="btn btn-primary">Simpan Invoice</button></div>
  </form>
  <?= modal_close() ?>
  <?php endif; ?>
<?php endforeach; ?>
