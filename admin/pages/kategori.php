<?php
// Kategori Produk (Figma: 10 & 11)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $nama   = trim($_POST['nama'] ?? '');

    if ($action === 'hapus') {
        $used = db_one($conn, "SELECT COUNT(*) n FROM produk WHERE catg_id = ?", 'i', $id)['n'];
        if ($used) {
            flash('danger', "Kategori tidak dapat dihapus karena masih dipakai oleh <b>$used produk</b>.");
        } else {
            db_exec($conn, "DELETE FROM product_category WHERE id = ?", 'i', $id);
            flash('success', 'Kategori berhasil <b>dihapus</b>.');
        }
    } elseif ($nama === '') {
        flash('danger', 'Nama kategori wajib diisi.');
    } elseif (db_one($conn, "SELECT id FROM product_category WHERE nama = ? AND id <> ?", 'si', $nama, $id)) {
        flash('danger', 'Kategori dengan nama tersebut sudah ada.');
    } elseif ($action === 'tambah') {
        db_exec($conn, "INSERT INTO product_category (nama) VALUES (?)", 's', $nama);
        flash('success', 'Kategori berhasil <b>ditambahkan</b>.');
    } elseif ($action === 'edit') {
        db_exec($conn, "UPDATE product_category SET nama = ? WHERE id = ?", 'si', $nama, $id);
        flash('success', 'Kategori berhasil <b>diperbarui</b>.');
    }
    redirect('admin/index.php?page=kategori');
}

$rows = db_all($conn, "SELECT c.*, COUNT(p.id) AS jumlah FROM product_category c LEFT JOIN produk p ON p.catg_id = c.id GROUP BY c.id ORDER BY c.nama");

$form = function ($k = null) {
    return '<form method="post">' . csrf_field()
        . '<input type="hidden" name="action" value="' . ($k ? 'edit' : 'tambah') . '"><input type="hidden" name="id" value="' . (int)($k['id'] ?? 0) . '">'
        . '<div class="modal-body"><label class="field"><span class="label">Nama Kategori</span>'
        . '<input class="input" name="nama" value="' . e($k['nama'] ?? '') . '" placeholder="Contoh: Aksesoris" required></label></div>'
        . '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button>'
        . '<button type="submit" class="btn btn-primary">' . ($k ? 'Simpan Perubahan' : 'Simpan') . '</button></div></form>';
};
?>
<?= page_head('Data Kategori Produk', 'Kelompokkan produk berdasarkan kategori.', '<button type="button" class="btn btn-primary" data-open="modalTambah">' . ic('plus', 'icon icon-sm') . '<span>Tambah Kategori</span></button>') ?>
<?= render_flash() ?>

<div class="card">
  <?php if (!$rows): ?>
    <div class="empty"><?= ic('tag', 'icon icon-xl') ?>Belum ada kategori.</div>
  <?php else: ?>
  <div class="table">
    <div class="tr thead"><div class="th" style="width:80px">No</div><div class="th" style="width:100px">ID</div><div class="th" style="flex:1">Nama Kategori</div><div class="th" style="width:200px">Jumlah Produk</div><div class="th" style="width:200px">Aksi</div></div>
    <?php foreach ($rows as $i => $k): ?>
    <div class="tr">
      <div class="td" style="width:80px"><?= $i + 1 ?></div>
      <div class="td muted" style="width:100px"><?= (int)$k['id'] ?></div>
      <div class="td semibold" style="flex:1"><?= e($k['nama']) ?></div>
      <div class="td" style="width:200px"><?= (int)$k['jumlah'] ?> produk</div>
      <div class="td" style="width:200px">
        <button type="button" class="btn btn-warning btn-sm" data-open="modalEdit<?= (int)$k['id'] ?>"><?= ic('edit', 'icon icon-sm') ?><span>Ubah</span></button>
        <?= delete_button('hapus', $k['id'], 'Kategori "' . $k['nama'] . '" akan dihapus.') ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?= modal_open('modalTambah', 'Tambah Kategori Produk') . $form() . modal_close() ?>
<?php foreach ($rows as $k): ?>
  <?= modal_open('modalEdit' . (int)$k['id'], 'Ubah Kategori Produk') . $form($k) . modal_close() ?>
<?php endforeach; ?>
