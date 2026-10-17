<?php
$title = 'Registrasi Pelanggan';
$desc  = 'Form pendaftaran akun pelanggan baru.';
?>
<div class="frame">
  <?= nav_public('register') ?>
  <div class="auth-wrap">
    <div class="card card-shadow auth-card" style="width:520px">
      <div class="col items-center gap-12 text-center">
        <div class="feature-icon" style="width:56px;height:56px"><?= ic('user', 'icon icon-lg') ?></div>
        <div class="h2">Registrasi Akun Baru</div>
        <div class="body">Daftar untuk menjadi pelanggan kami dan mulai booking layanan!</div>
      </div>
      <?= field('Username', input('Masukkan username', true)) ?>
      <?= field('Password', input('Minimal 8 karakter', true)) ?>
      <?= field('Nama Lengkap', input('Nama sesuai identitas', true)) ?>
      <?= field('Email', input('nama@email.com', true)) ?>
      <?= field('Nomor HP', input('08xx-xxxx-xxxx', true), 'Opsional — untuk menghubungi Anda terkait servis') ?>
      <?= btn('Daftar Akun', 'primary', null, 'btn-block btn-lg') ?>
      <div class="body text-center">Sudah punya akun? <span class="semibold" style="color:#0d6efd">Login di sini</span></div>
    </div>
  </div>
</div>
