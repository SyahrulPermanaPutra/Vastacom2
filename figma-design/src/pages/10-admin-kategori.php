<?php
$title = 'Admin — Kategori Produk';
$desc  = 'Tabel kategori produk + modal tambah & edit kategori.';
$rows = [];
foreach ([[1, 'Laptop', 12], [2, 'Hardware', 8], [3, 'Aksesoris', 5], [4, 'Software', 2]] as $i => $k) {
    $rows[] = [$i + 1, '<span class="muted">' . $k[0] . '</span>', '<span class="semibold">' . $k[1] . '</span>', $k[2] . ' produk', aksi_edit_hapus()];
}
$content = page_head('Data Kategori Produk', 'Kelompokkan produk berdasarkan kategori.', btn('Tambah Kategori', 'primary', 'plus'))
    . '<div class="card">' . table([['No', 80], ['ID', 100], ['Nama Kategori', null], ['Jumlah Produk', 200], ['Aksi', 200]], $rows) . '</div>';
echo admin_frame('kategori', $content);
