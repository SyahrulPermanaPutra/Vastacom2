<?php
// Data User (Figma: 12 & 13). Akun pelanggan lengkap dikelola dari menu Pelanggan.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $id       = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if ($action === 'hapus') {
        if ($id === (int)$me['id']) {
            flash('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
        } else {
            try {
                $conn->begin_transaction();
                db_exec($conn, "DELETE FROM pelanggan WHERE user_id = ? AND NOT EXISTS (SELECT 1 FROM service WHERE service.id_pelanggan = pelanggan.id)", 'i', $id);
                db_exec($conn, "DELETE FROM users WHERE id = ?", 'i', $id);
                $conn->commit();
                flash('success', 'User berhasil <b>dihapus</b>.');
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                flash('danger', 'User tidak dapat dihapus karena masih memiliki data service atau invoice terkait.');
            }
        }
        redirect('admin/index.php?page=users');
    }

    $error = null;
    if ($username === '' || !isset(ROLE_LABEL[$role])) $error = 'Username dan role wajib diisi.';
    elseif (db_one($conn, "SELECT id FROM users WHERE username = ? AND id <> ?", 'si', $username, $id)) $error = 'Username sudah digunakan.';
    elseif (($action === 'tambah' || $password !== '') && strlen($password) < 6) $error = 'Password minimal 6 karakter.';
    elseif ($action === 'edit' && $id === (int)$me['id'] && $role !== 'admin') $error = 'Anda tidak dapat menurunkan role akun Anda sendiri.';

    if ($error) {
        flash('danger', $error);
    } elseif ($action === 'tambah') {
        $conn->begin_transaction();
        db_exec($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)", 'sss', $username, password_hash($password, PASSWORD_DEFAULT), $role);
        if ($role === 'pelanggan') {
            db_exec($conn, "INSERT INTO pelanggan (user_id, nama) VALUES (?, ?)", 'is', $conn->insert_id, $username);
        }
        $conn->commit();
        flash('success', 'User <b>' . e($username) . '</b> berhasil ditambahkan.');
    } elseif ($action === 'edit') {
        db_exec($conn, "UPDATE users SET username = ?, role = ? WHERE id = ?", 'ssi', $username, $role, $id);
        if ($password !== '') {
            db_exec($conn, "UPDATE users SET password = ? WHERE id = ?", 'si', password_hash($password, PASSWORD_DEFAULT), $id);
        }
        if ($role === 'pelanggan' && !db_one($conn, "SELECT id FROM pelanggan WHERE user_id = ?", 'i', $id)) {
            db_exec($conn, "INSERT INTO pelanggan (user_id, nama) VALUES (?, ?)", 'is', $id, $username);
        }
        flash('success', 'User <b>' . e($username) . '</b> berhasil diperbarui.');
    }
    redirect('admin/index.php?page=users');
}

$rows = db_all($conn, "SELECT id, username, role, created_at FROM users ORDER BY FIELD(role, 'admin', 'teknisi', 'pelanggan'), username");

$form = function ($u = null) {
    $h = '<form method="post">' . csrf_field()
        . '<input type="hidden" name="action" value="' . ($u ? 'edit' : 'tambah') . '"><input type="hidden" name="id" value="' . (int)($u['id'] ?? 0) . '">'
        . '<div class="modal-body">'
        . '<label class="field"><span class="label">Username</span><input class="input" name="username" value="' . e($u['username'] ?? '') . '" placeholder="Masukkan username" required></label>'
        . '<label class="field"><span class="label">Password</span><input class="input" type="password" name="password" minlength="6" placeholder="' . ($u ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter') . '"' . ($u ? '' : ' required') . '></label>'
        . '<div class="field"><span class="label">Role</span><div class="radio-group">';
    foreach (ROLE_LABEL as $value => $label) {
        $checked = ($u['role'] ?? 'teknisi') === $value;
        $h .= '<label class="radio"><input type="radio" name="role" value="' . $value . '"' . ($checked ? ' checked' : '') . '><span>' . $label . '</span></label>';
    }
    return $h . '</div><span class="help">Untuk pelanggan, lengkapi nama, email & no. HP lewat menu Pelanggan.</span></div></div>'
        . '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div></form>';
};
?>
<?= page_head('Data User', 'Kelola akun yang dapat masuk ke sistem.', '<button type="button" class="btn btn-success" data-open="modalTambah">' . ic('plus', 'icon icon-sm') . '<span>Tambah User</span></button>') ?>
<?= render_flash() ?>

<div class="card">
  <div class="table">
    <div class="tr thead"><div class="th" style="width:90px">ID</div><div class="th" style="flex:1">Username</div><div class="th" style="width:220px">Role</div><div class="th" style="width:200px">Dibuat</div><div class="th" style="width:200px">Aksi</div></div>
    <?php foreach ($rows as $u): ?>
    <div class="tr">
      <div class="td muted" style="width:90px"><?= (int)$u['id'] ?></div>
      <div class="td" style="flex:1"><span class="avatar"><?= e(initials($u['username'])) ?></span><span class="semibold"><?= e($u['username']) ?></span><?= (int)$u['id'] === (int)$me['id'] ? '<span class="small muted">(Anda)</span>' : '' ?></div>
      <div class="td" style="width:220px"><?= badge($u['role'], ROLE_LABEL[$u['role']]) ?></div>
      <div class="td muted" style="width:200px"><?= tgl($u['created_at'], false) ?></div>
      <div class="td" style="width:200px">
        <button type="button" class="btn btn-warning btn-sm" data-open="modalEdit<?= (int)$u['id'] ?>"><?= ic('edit', 'icon icon-sm') ?><span>Edit</span></button>
        <?php if ((int)$u['id'] !== (int)$me['id']): ?><?= delete_button('hapus', $u['id'], 'Akun "' . $u['username'] . '" akan dihapus.') ?><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?= modal_open('modalTambah', 'Tambah User') . $form() . modal_close() ?>
<?php foreach ($rows as $u): ?>
  <?= modal_open('modalEdit' . (int)$u['id'], 'Edit User') . $form($u) . modal_close() ?>
<?php endforeach; ?>
