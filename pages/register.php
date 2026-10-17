<?php
// Registrasi pelanggan (Figma: 03-registrasi)
$old = array_map('trim', array_intersect_key($_POST, array_flip(['username', 'nama', 'email', 'no_hp']))) + ['username' => '', 'nama' => '', 'email' => '', 'no_hp' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUserAsPelanggan($conn, $old['username'], $_POST['password'] ?? '', $old['nama'], $old['email'], $old['no_hp']);
    if ($result['status']) {
        flash('success', $result['message']);
        redirect('index.php?page=login');
    }
    flash('danger', $result['message']);
}
?>
<div class="auth-wrap">
  <form method="post" class="card card-shadow auth-card" style="width:520px;max-width:calc(100% - 32px)">
    <?= csrf_field() ?>
    <div class="col items-center gap-12 text-center">
      <div class="feature-icon" style="width:56px;height:56px"><?= ic('user', 'icon icon-lg') ?></div>
      <h1 class="h2">Registrasi Akun Baru</h1>
      <p class="body">Daftar untuk menjadi pelanggan kami dan pantau status servis Anda!</p>
    </div>
    <?= render_flash() ?>
    <label class="field"><span class="label">Username</span>
      <input class="input" type="text" name="username" value="<?= e($old['username']) ?>" placeholder="Masukkan username" required></label>
    <label class="field"><span class="label">Password</span>
      <input class="input" type="password" name="password" placeholder="Minimal 6 karakter" minlength="6" required></label>
    <label class="field"><span class="label">Nama Lengkap</span>
      <input class="input" type="text" name="nama" value="<?= e($old['nama']) ?>" placeholder="Nama sesuai identitas" required></label>
    <label class="field"><span class="label">Email</span>
      <input class="input" type="email" name="email" value="<?= e($old['email']) ?>" placeholder="nama@email.com" required></label>
    <label class="field"><span class="label">Nomor HP</span>
      <input class="input" type="text" name="no_hp" value="<?= e($old['no_hp']) ?>" placeholder="08xx-xxxx-xxxx">
      <span class="help">Opsional — untuk menghubungi Anda terkait servis</span></label>
    <button type="submit" class="btn btn-primary btn-lg btn-block">Daftar Akun</button>
    <p class="body text-center">Sudah punya akun? <a class="link" href="<?= e(url('index.php?page=login')) ?>">Login di sini</a></p>
  </form>
</div>
