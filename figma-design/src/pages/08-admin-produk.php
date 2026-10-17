<?php
$title = 'Admin — Data Produk';
$desc  = 'Tabel produk dengan filter kategori, jumlah data per halaman & pagination.';
$rows = [];
foreach ($PRODUK as $i => $p) {
    $rows[] = [$i + 1, '<span class="semibold">' . $p[0] . '</span>', rp($p[1]), thumb(), $p[2], $p[3] ? badge('aktif', 'Aktif') : badge('nonaktif', 'Nonaktif'), aksi_edit_hapus()];
}
$content = page_head('Data Produk', 'Kelola produk yang tampil di halaman utama.', btn('Tambah Produk', 'primary', 'plus'))
    . '<div class="card">'
    . '<div class="card-body" style="flex-direction:row;justify-content:space-between;align-items:center">'
    .   '<div class="input-group" style="width:420px">' . input('Cari nama produk...', true) . btn('Cari', 'outline-secondary', 'search') . '</div>'
    .   '<div class="row gap-12">' . select('-- Semua Kategori --', false, '') . select('10 Data', false, '') . '</div>'
    . '</div>'
    . table([['No', 70], ['Nama', null], ['Harga', 170], ['Gambar', 140], ['Kategori', 150], ['Status', 140], ['Aksi', 200]], $rows)
    . '<div class="card-body" style="flex-direction:row;justify-content:space-between;align-items:center;border-top:1px solid #e2e8f0"><div class="small muted">Menampilkan 1–8 dari 8 produk</div>' . pagination(1, 1) . '</div>'
    . '</div>';
echo admin_frame('produk', $content);
