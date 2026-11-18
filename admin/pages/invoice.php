<?php
// Manajemen Invoice (Figma: 20 & 21)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'tambah') {
        $error = create_invoice($conn, $_POST, $me['id']);
        $error ? flash('danger', $error) : flash('success', 'Invoice berhasil <b>ditambahkan</b>.');
    } elseif ($action === 'edit' && ($inv = db_one($conn, "SELECT * FROM invoice WHERE id = ?", 'i', $id))) {
        $status = $_POST['status_pembayaran'] ?? 'pending';
        $total  = (float)($_POST['total_biaya'] ?? 0);
        $metode = $_POST['metode_pembayaran'] ?? '';
        if (!isset(STATUS_BAYAR[$status]) || $total <= 0) {
            flash('danger', 'Total biaya dan status pembayaran harus valid.');
        } elseif (!in_array($metode, METODE_BAYAR, true) && $metode !== (string)$inv['metode_pembayaran']) {
            flash('danger', 'Pilih metode pembayaran.');
        } else {
            // Tanggal pembayaran diisi saat pertama kali lunas, dikosongkan bila status bukan lunas
            $bayar = $status === 'lunas' ? ($inv['tanggal_pembayaran'] ?: date('Y-m-d H:i:s')) : null;
            db_exec($conn, "UPDATE invoice SET total_biaya = ?, metode_pembayaran = ?, keterangan = ?, status_pembayaran = ?, tanggal_pembayaran = ? WHERE id = ?",
                'dssssi', $total, $metode, trim($_POST['keterangan'] ?? ''), $status, $bayar, $id);
            flash('success', 'Invoice <b>INV-' . $id . '</b> berhasil diperbarui.');
        }
    } elseif ($action === 'hapus') {
        db_exec($conn, "DELETE FROM invoice WHERE id = ?", 'i', $id);
        flash('success', 'Invoice berhasil <b>dihapus</b>.');
    }
    redirect('admin/index.php' . qs());
}

$status = isset(STATUS_BAYAR[$_GET['status'] ?? '']) ? $_GET['status'] : '';
$where  = $status ? " WHERE i.status_pembayaran = '" . $status . "'" : '';
$total  = db_one($conn, "SELECT COUNT(*) n FROM invoice i" . $where)['n'];
[$limit, $pageNo, $offset, $pages] = paginate($total);
$rows = db_all($conn, "SELECT i.*, s.jenis_kerusakan, p.nama AS nama_pelanggan, u.username AS pembuat
                       FROM invoice i JOIN service s ON i.service_id = s.id JOIN pelanggan p ON s.id_pelanggan = p.id
                       LEFT JOIN users u ON i.id_admin_pembuat = u.id" . $where . "
                       ORDER BY i.tanggal_invoice DESC LIMIT $limit OFFSET $offset");
$sum = db_one($conn, "SELECT COUNT(*) jml,
                      COALESCE(SUM(CASE WHEN status_pembayaran = 'lunas' THEN total_biaya END), 0) lunas,
                      COALESCE(SUM(CASE WHEN status_pembayaran = 'pending' THEN total_biaya END), 0) pending FROM invoice");
$siapInvoice = services_without_invoice($conn);

$statusOptions = function ($selected) {
    $h = '';
    foreach (STATUS_BAYAR as $k => [$label]) $h .= '<option value="' . $k . '"' . ($selected === $k ? ' selected' : '') . '>' . $label . '</option>';
    return $h;
};
$fields = function ($inv = null) use ($statusOptions) {
    return '<label class="field"><span class="label">Total Biaya (Rp)</span><input class="input" type="number" name="total_biaya" min="1" step="1000" value="' . e($inv ? (float)$inv['total_biaya'] : '') . '" placeholder="Total biaya" required></label>'
        . '<label class="field"><span class="label">Metode Pembayaran</span><select class="input" name="metode_pembayaran" required>' . metode_options((string)($inv['metode_pembayaran'] ?? '')) . '</select></label>'
        . '<label class="field"><span class="label">Keterangan</span><textarea class="input" name="keterangan" rows="3" placeholder="Keterangan">' . e($inv['keterangan'] ?? '') . '</textarea></label>'
        . '<label class="field"><span class="label">Status Pembayaran</span><select class="input" name="status_pembayaran">' . $statusOptions($inv['status_pembayaran'] ?? 'pending') . '</select></label>';
};
$cards = [
    ['Total Invoice', (int)$sum['jml'], 'receipt', '#0d6efd'],
    ['Lunas', rp($sum['lunas']), 'check', '#198754'],
    ['Belum Lunas', rp($sum['pending']), 'clock', '#b58100'],
];
?>
<?= page_head('Manajemen Invoice', 'Tagihan untuk setiap servis yang telah dikerjakan.', '<button type="button" class="btn btn-success" data-open="modalTambah">' . ic('plus', 'icon icon-sm') . '<span>Tambah Invoice</span></button>') ?>
<?= render_flash() ?>

<div class="row gap-20 card-grid">
  <?php foreach ($cards as [$label, $value, $icon, $color]): ?>
  <div class="card grow"><div class="card-body" style="flex-direction:row;align-items:center">
    <div class="stat-icon" style="background:<?= $color ?>1a;color:<?= $color ?>"><?= ic($icon) ?></div>
    <div class="col gap-4"><div class="small muted semibold"><?= $label ?></div><div class="h2"><?= e($value) ?></div></div>
  </div></div>
  <?php endforeach; ?>
</div>

<div class="card">
  <form method="get" class="card-body toolbar">
    <input type="hidden" name="page" value="invoice">
    <div class="small muted"><?= (int)count($siapInvoice) ?> servis selesai belum dibuatkan invoice.</div>
    <select class="input input-sm" name="status" data-autosubmit>
      <option value="">Semua Status</option>
      <?php foreach (STATUS_BAYAR as $k => [$label]): ?><option value="<?= $k ?>"<?= $status === $k ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
    </select>
  </form>
  <?php if (!$rows): ?>
    <div class="empty"><?= ic('receipt', 'icon icon-xl') ?>Belum ada invoice.</div>
  <?php else: ?>
  <div class="table-wrap"><div class="table">
    <div class="tr thead">
      <div class="th" style="width:60px">No</div><div class="th" style="width:110px">No. Invoice</div><div class="th" style="flex:1">Servis</div><div class="th" style="width:130px">Tanggal</div>
      <div class="th" style="width:160px">Total</div><div class="th" style="width:130px">Status</div><div class="th" style="width:110px">Pembuat</div><div class="th" style="width:290px">Aksi</div>
    </div>
    <?php foreach ($rows as $i => $inv): ?>
    <div class="tr">
      <div class="td" style="width:60px"><?= $offset + $i + 1 ?></div>
      <div class="td semibold" style="width:110px">INV-<?= (int)$inv['id'] ?></div>
      <div class="td" style="flex:1;flex-direction:column;align-items:flex-start;gap:2px"><span class="semibold"><?= e($inv['jenis_kerusakan']) ?></span><span class="small muted">#SVC-<?= (int)$inv['service_id'] ?> · <?= e($inv['nama_pelanggan']) ?></span></div>
      <div class="td" style="width:130px"><?= tgl($inv['tanggal_invoice'], false) ?></div>
      <div class="td semibold" style="width:160px"><?= rp($inv['total_biaya']) ?></div>
      <div class="td" style="width:130px"><?= badge_bayar($inv['status_pembayaran']) ?></div>
      <div class="td" style="width:110px"><?= e($inv['pembuat'] ?: '-') ?></div>
      <div class="td" style="width:290px">
        <a class="btn btn-primary btn-sm" target="_blank" href="<?= e(url('admin/index.php?page=invoice_cetak&id=' . (int)$inv['id'])) ?>"><?= ic('printer', 'icon icon-sm') ?><span>Cetak</span></a>
        <button type="button" class="btn btn-warning btn-sm" data-open="modalEdit<?= (int)$inv['id'] ?>"><?= ic('edit', 'icon icon-sm') ?><span>Ubah</span></button>
        <?= delete_button('hapus', $inv['id'], 'Invoice INV-' . $inv['id'] . ' (' . $inv['nama_pelanggan'] . ') akan dihapus.') ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>
  <div class="card-body toolbar" style="border-top:1px solid var(--border)">
    <div class="small muted">Menampilkan <?= $total ? ($offset + 1) . '–' . ($offset + count($rows)) : 0 ?> dari <?= (int)$total ?> invoice</div>
    <?= pagination_links($pageNo, $pages) ?>
  </div>
</div>

<?= modal_open('modalTambah', 'Tambah Invoice') ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="tambah">
  <div class="modal-body">
    <?php if (!$siapInvoice): ?>
      <div class="alert alert-info"><?= ic('alert') ?><span>Tidak ada servis berstatus <b>Selesai</b> yang belum memiliki invoice.</span></div>
    <?php else: ?>
    <label class="field"><span class="label">Servis</span><select class="input" name="service_id" required><option value="">-- Pilih servis --</option>
      <?php foreach ($siapInvoice as $s): ?><option value="<?= (int)$s['id'] ?>">#SVC-<?= (int)$s['id'] ?> — <?= e($s['nama_pelanggan']) ?> · <?= e($s['jenis_kerusakan']) ?></option><?php endforeach; ?>
    </select><span class="help">Hanya servis berstatus Selesai yang belum memiliki invoice.</span></label>
    <?= $fields() ?>
    <?php endif; ?>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button><?php if ($siapInvoice): ?><button type="submit" class="btn btn-success">Simpan</button><?php endif; ?></div>
</form>
<?= modal_close() ?>

<?php foreach ($rows as $inv): ?>
<?= modal_open('modalEdit' . (int)$inv['id'], 'Ubah Invoice INV-' . (int)$inv['id'], '', 'modal-header-warning') ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= (int)$inv['id'] ?>">
  <div class="modal-body"><?= $fields($inv) ?></div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
</form>
<?= modal_close() ?>
<?php endforeach; ?>
