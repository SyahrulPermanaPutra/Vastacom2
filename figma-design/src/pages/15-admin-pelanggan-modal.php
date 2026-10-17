<?php
$title = 'Admin — Modal Tambah & Edit Pelanggan';
$desc  = 'Form tambah dan edit data pelanggan.';
$tambah = field('Nama Lengkap', input('Nama Pelanggan', true)) . field('Email', input('Email Pelanggan', true)) . field('Nomor HP', input('Nomor HP', true));
$edit   = field('Nama Lengkap', input('Budi Santoso')) . field('Email', input('budi@email.com')) . field('Nomor HP', input('0812-3456-7890'));
?>
<div class="frame">
  <?= overlay(
      modal('Tambah Pelanggan', $tambah, btn('Batal', 'secondary') . btn('Simpan', 'primary')),
      modal('Edit Pelanggan', $edit, btn('Batal', 'secondary') . btn('Simpan Perubahan', 'primary'))
  ) ?>
</div>
