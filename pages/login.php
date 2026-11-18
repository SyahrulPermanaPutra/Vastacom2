<?php
// Login untuk semua role (Figma: 02-login). Admin & teknisi diarahkan ke panel, pelanggan ke dashboard.
$username = trim($_POST['username'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser($conn, $username, $_POST['password'] ?? '');
    if ($result['status']) {
        redirect(homeForRole($result['role']));
    }
    flash('danger', $result['message']);
}
?>
<div class="auth-wrap">
  <form method="post" class="card card-shadow auth-card">
    <?= csrf_field() ?>
    <div class="col items-center gap-12 text-center">
      <div class="feature-icon" style="width:56px;height:56px"><?= ic('lock', 'icon icon-lg') ?></div>
      <h1 class="h2">Masuk ke Akun Anda</h1>
      <p class="body">Pelanggan, teknisi, dan admin masuk melalui halaman ini.</p>
    </div>
    <?= render_flash() ?>
    <label class="field"><span class="label">Nama Pengguna</span>
      <input class="input" type="text" name="username" value="<?= e($username) ?>" required autofocus autocomplete="username"></label>
    <label class="field"><span class="label">Kata Sandi</span>
      <input class="input" type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn btn-primary btn-lg btn-block">Masuk</button>
    <p class="body text-center">Belum punya akun? Akun pelanggan dibuatkan oleh admin saat Anda menyerahkan perangkat di toko.</p>
  </form>
</div>
