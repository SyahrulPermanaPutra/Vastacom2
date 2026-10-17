<?php
$title = 'Login (Semua Role)';
$desc  = 'Satu halaman login untuk pelanggan, teknisi & admin (diarahkan sesuai role).';
?>
<div class="frame" style="min-height:900px">
  <?= nav_public('login') ?>
  <div class="auth-wrap">
    <div class="card card-shadow auth-card">
      <div class="col items-center gap-12 text-center">
        <div class="feature-icon" style="width:56px;height:56px"><?= ic('lock', 'icon icon-lg') ?></div>
        <div class="h2">Login ke Akun Anda</div>
        <div class="body">Pelanggan, teknisi, dan admin masuk melalui halaman ini.</div>
      </div>
      <div class="alert alert-danger"><?= ic('alert') ?><span>Username atau password salah.</span></div>
      <?= field('Username', input('budi')) ?>
      <?= field('Password', input('••••••••', false, 'focus')) ?>
      <?= btn('Login', 'primary', null, 'btn-block btn-lg') ?>
      <div class="body text-center">Belum punya akun? <span class="semibold" style="color:#0d6efd">Daftar di sini</span></div>
    </div>
  </div>
</div>
