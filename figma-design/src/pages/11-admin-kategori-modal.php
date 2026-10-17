<?php
$title = 'Admin — Modal Tambah & Edit Kategori';
$desc  = 'Form tambah dan edit nama kategori produk.';
?>
<div class="frame">
  <?= overlay(
      modal('Tambah Kategori Produk', field('Nama Kategori', input('Contoh: Aksesoris', true)), btn('Batal', 'secondary') . btn('Simpan', 'primary')),
      modal('Edit Kategori Produk', field('Nama Kategori', input('Hardware', false, 'focus')), btn('Batal', 'secondary') . btn('Simpan Perubahan', 'primary'))
  ) ?>
</div>
