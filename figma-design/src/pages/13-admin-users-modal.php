<?php
$title = 'Admin — Modal Tambah & Edit User';
$desc  = 'Form user: username, password & pilihan role.';
$help  = 'Untuk pelanggan, lengkapi nama, email & no. HP lewat menu Pelanggan.';
$tambah = field('Username', input('Masukkan username', true))
    . field('Password', input('Minimal 6 karakter', true))
    . field('Role', radios(['Admin', 'Teknisi', 'Pelanggan'], 1), $help);
$edit = field('Username', input('teknisi_andi'))
    . field('Password', input('Kosongkan jika tidak diubah', true))
    . field('Role', radios(['Admin', 'Teknisi', 'Pelanggan'], 1), $help);
?>
<div class="frame">
  <?= overlay(
      modal('Tambah User', $tambah, btn('Batal', 'secondary') . btn('Simpan', 'primary')),
      modal('Edit User', $edit, btn('Batal', 'secondary') . btn('Simpan', 'primary'))
  ) ?>
</div>
