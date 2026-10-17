<?php
$title = 'Admin — Modal Tambah & Edit Produk';
$desc  = 'Form tambah produk dan edit produk (dengan preview gambar saat ini).';
$kategori = ['Laptop', 'Hardware', 'Aksesoris'];
$tambah = field('Nama Produk', input('Nama Produk', true))
    . field('Harga', input('Harga', true))
    . field('Kategori', radios($kategori))
    . field('Gambar Produk', file_input(), 'Format: JPG, JPEG, PNG, WEBP — maks. 2 MB')
    . field('Status', select('Aktif'));
$edit = field('Nama Produk', input('Lenovo Ideapad Gaming'))
    . field('Harga', input('13899000'))
    . field('Kategori', radios($kategori, 0))
    . field('Gambar Produk', file_input())
    . '<div class="row gap-12 items-center"><div class="thumb" style="width:120px;height:84px">' . ic('laptop', 'icon icon-lg') . '</div><div class="small muted">Gambar saat ini</div></div>'
    . field('Status', select('Aktif'));
$footer = function ($save) { return btn('Batal', 'secondary') . btn($save, 'primary'); };
?>
<div class="frame">
  <?= overlay(modal('Tambah Produk', $tambah, $footer('Simpan')), modal('Edit Produk', $edit, $footer('Simpan Perubahan'))) ?>
</div>
