<?php
// Data Produk (Figma: 08 & 09)
$imgDir   = ROOT_PATH . '/assets/image';
$kategori = db_all($conn, "SELECT * FROM product_category ORDER BY nama");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'hapus') {
        $old = db_one($conn, "SELECT gambar FROM produk WHERE id = ?", 'i', $id);
        if ($old && db_exec($conn, "DELETE FROM produk WHERE id = ?", 'i', $id)) {
            delete_file($imgDir, $old['gambar']);
            flash('success', 'Produk berhasil <b>dihapus</b>.');
        }
        redirect('admin/index.php' . qs());
    }

    $nama   = trim($_POST['nama'] ?? '');
    $harga  = (float)($_POST['harga'] ?? 0);
    $catg   = (int)($_POST['catg_id'] ?? 0) ?: null;
    $status = ($_POST['status'] ?? '1') === '0' ? '0' : '1';
    $error  = $nama === '' ? 'Nama produk wajib diisi.' : ($harga < 0 ? 'Harga tidak valid.' : null);

    $gambar = $error ? '' : upload_image('gambar', $imgDir);
    if ($gambar === false) $error = 'Gambar gagal diunggah. Gunakan file JPG, JPEG, PNG, GIF, atau WEBP.';

    if ($error) {
        flash('danger', $error);
    } elseif ($action === 'tambah') {
        db_exec($conn, "INSERT INTO produk (nama, harga, catg_id, status, gambar) VALUES (?, ?, ?, ?, ?)", 'sdiss', $nama, $harga, $catg, $status, $gambar);
        flash('success', 'Produk berhasil <b>ditambahkan</b>.');
    } elseif ($action === 'edit' && ($old = db_one($conn, "SELECT gambar FROM produk WHERE id = ?", 'i', $id))) {
        if ($gambar) delete_file($imgDir, $old['gambar']);
        db_exec($conn, "UPDATE produk SET nama = ?, harga = ?, catg_id = ?, status = ?, gambar = ? WHERE id = ?", 'sdissi',
            $nama, $harga, $catg, $status, $gambar ?: $old['gambar'], $id);
        flash('success', 'Produk berhasil <b>diperbarui</b>.');
    }
    redirect('admin/index.php' . qs());
}

// ---- Data tabel: pencarian, filter kategori, pagination ----
$q      = trim($_GET['q'] ?? '');
$filter = (int)($_GET['kategori'] ?? 0);
$where  = " WHERE p.nama LIKE ?" . ($filter ? " AND p.catg_id = " . $filter : '');
$like   = '%' . $q . '%';
$total  = db_one($conn, "SELECT COUNT(*) n FROM produk p" . $where, 's', $like)['n'];
[$limit, $pageNo, $offset, $pages] = paginate($total);
$rows = db_all($conn, "SELECT p.*, c.nama AS kategori FROM produk p LEFT JOIN product_category c ON p.catg_id = c.id"
    . $where . " ORDER BY p.id DESC LIMIT $limit OFFSET $offset", 's', $like);

$form = function ($p = null) use ($kategori) {
    $id = $p ? (int)$p['id'] : 0;
    $h  = '<form method="post" enctype="multipart/form-data">' . csrf_field()
        . '<input type="hidden" name="action" value="' . ($p ? 'edit' : 'tambah') . '"><input type="hidden" name="id" value="' . $id . '">'
        . '<div class="modal-body">'
        . '<label class="field"><span class="label">Nama Produk</span><input class="input" name="nama" value="' . e($p['nama'] ?? '') . '" placeholder="Nama Produk" required></label>'
        . '<label class="field"><span class="label">Harga</span><input class="input" type="number" name="harga" min="0" step="1000" value="' . e($p ? (float)$p['harga'] : '') . '" placeholder="Harga" required></label>'
        . '<div class="field"><span class="label">Kategori</span><div class="radio-group" style="flex-wrap:wrap">';
    foreach ($kategori as $i => $k) {
        $checked = $p ? (int)$p['catg_id'] === (int)$k['id'] : $i === 0;
        $h .= '<label class="radio"><input type="radio" name="catg_id" value="' . (int)$k['id'] . '"' . ($checked ? ' checked' : '') . '><span>' . e($k['nama']) . '</span></label>';
    }
    $h .= '</div></div>'
        . '<label class="field"><span class="label">Gambar Produk</span><input class="input input-file" type="file" name="gambar" accept=".jpg,.jpeg,.png,.gif,.webp">'
        . '<span class="help">Format: JPG, JPEG, PNG, GIF, WEBP' . ($p ? ' — kosongkan jika tidak diganti' : '') . '</span></label>'
        . ($p ? '<div class="row gap-12 items-center"><div class="thumb" style="width:120px;height:84px"><img class="thumb-img" src="' . e(product_image_url($p['gambar'])) . '" alt=""></div><div class="small muted">Gambar saat ini</div></div>' : '')
        . '<label class="field"><span class="label">Status</span><select class="input" name="status">'
        . '<option value="1"' . (($p['status'] ?? '1') === '1' ? ' selected' : '') . '>Aktif</option><option value="0"' . (($p['status'] ?? '1') === '0' ? ' selected' : '') . '>Nonaktif</option></select></label>'
        . '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button><button type="submit" class="btn btn-primary">' . ($p ? 'Simpan Perubahan' : 'Simpan') . '</button></div></form>';
    return $h;
};
?>
<?= page_head('Data Produk', 'Kelola produk yang tampil di halaman utama.', '<button type="button" class="btn btn-primary" data-open="modalTambah">' . ic('plus', 'icon icon-sm') . '<span>Tambah Produk</span></button>') ?>
<?= render_flash() ?>

<div class="card">
  <form method="get" class="card-body toolbar">
    <input type="hidden" name="page" value="produk">
    <div class="input-group" style="width:420px">
      <input class="input" name="q" value="<?= e($q) ?>" placeholder="Cari nama produk...">
      <button class="btn btn-outline-secondary" type="submit"><?= ic('search', 'icon icon-sm') ?><span>Cari</span></button>
    </div>
    <div class="row gap-12">
      <select class="input input-sm" name="kategori" data-autosubmit>
        <option value="">-- Semua Kategori --</option>
        <?php foreach ($kategori as $k): ?><option value="<?= (int)$k['id'] ?>"<?= $filter === (int)$k['id'] ? ' selected' : '' ?>><?= e($k['nama']) ?></option><?php endforeach; ?>
      </select>
      <select class="input input-sm" name="limit" data-autosubmit>
        <?php foreach ([10, 20, 50, 100] as $n): ?><option value="<?= $n ?>"<?= $limit === $n ? ' selected' : '' ?>><?= $n ?> Data</option><?php endforeach; ?>
      </select>
    </div>
  </form>

  <?php if (!$rows): ?>
    <div class="empty"><?= ic('package', 'icon icon-xl') ?>Tidak ada produk<?= $q || $filter ? ' yang cocok dengan filter' : '' ?>.</div>
  <?php else: ?>
  <div class="table-wrap"><div class="table">
    <div class="tr thead">
      <div class="th" style="width:70px">No</div><div class="th" style="flex:1">Nama</div><div class="th" style="width:170px">Harga</div><div class="th" style="width:140px">Gambar</div>
      <div class="th" style="width:150px">Kategori</div><div class="th" style="width:140px">Status</div><div class="th" style="width:200px">Aksi</div>
    </div>
    <?php foreach ($rows as $i => $p): ?>
    <div class="tr">
      <div class="td" style="width:70px"><?= $offset + $i + 1 ?></div>
      <div class="td semibold" style="flex:1"><?= e($p['nama']) ?></div>
      <div class="td" style="width:170px"><?= rp($p['harga']) ?></div>
      <div class="td" style="width:140px"><div class="thumb"><img class="thumb-img" src="<?= e(product_image_url($p['gambar'])) ?>" alt="" loading="lazy"></div></div>
      <div class="td" style="width:150px"><?= e($p['kategori'] ?? '-') ?></div>
      <div class="td" style="width:140px"><?= $p['status'] === '1' ? badge('aktif', 'Aktif') : badge('nonaktif', 'Nonaktif') ?></div>
      <div class="td" style="width:200px">
        <button type="button" class="btn btn-warning btn-sm" data-open="modalEdit<?= (int)$p['id'] ?>"><?= ic('edit', 'icon icon-sm') ?><span>Edit</span></button>
        <?= delete_button('hapus', $p['id'], 'Produk "' . $p['nama'] . '" akan dihapus permanen beserta gambarnya.') ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>

  <div class="card-body toolbar" style="border-top:1px solid var(--border)">
    <div class="small muted">Menampilkan <?= $total ? ($offset + 1) . '–' . ($offset + count($rows)) : 0 ?> dari <?= (int)$total ?> produk</div>
    <?= pagination_links($pageNo, $pages) ?>
  </div>
</div>

<?= modal_open('modalTambah', 'Tambah Produk') . $form() . modal_close() ?>
<?php foreach ($rows as $p): ?>
  <?= modal_open('modalEdit' . (int)$p['id'], 'Edit Produk') . $form($p) . modal_close() ?>
<?php endforeach; ?>
