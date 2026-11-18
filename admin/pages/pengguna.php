<?php
// Data Pengguna: gabungan akun (admin, teknisi, pelanggan) dan data pelanggan dalam satu halaman.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $id       = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $back     = 'admin/index.php' . qs();

    // Data pelanggan milik akun ini (bila ada) beserta jumlah servisnya
    $pel = $id ? db_one($conn, "SELECT p.*, (SELECT COUNT(*) FROM service s WHERE s.id_pelanggan = p.id) AS jml_service FROM pelanggan p WHERE p.user_id = ?", 'i', $id) : null;

    if ($action === 'hapus') {
        if ($id === (int)$me['id']) {
            flash('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
        } elseif ($pel && $pel['jml_service'] > 0) {
            flash('danger', 'Pengguna <b>' . e($pel['nama']) . '</b> tidak dapat dihapus karena memiliki ' . (int)$pel['jml_service'] . ' data servis.');
        } else {
            try {
                $conn->begin_transaction();
                db_exec($conn, "DELETE FROM pelanggan WHERE user_id = ?", 'i', $id);
                db_exec($conn, "DELETE FROM users WHERE id = ?", 'i', $id);
                $conn->commit();
                flash('success', 'Pengguna berhasil <b>dihapus</b>.');
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                flash('danger', 'Pengguna tidak dapat dihapus karena masih memiliki data servis atau invoice terkait.');
            }
        }
        redirect($back);
    }

    $error = null;
    if ($username === '' || !isset(ROLE_LABEL[$role])) $error = 'Nama pengguna dan role wajib diisi.';
    elseif (db_one($conn, "SELECT id FROM users WHERE username = ? AND id <> ?", 'si', $username, $id)) $error = 'Nama pengguna sudah dipakai.';
    elseif (($action === 'tambah' || $password !== '') && strlen($password) < 6) $error = 'Kata sandi minimal 6 karakter.';
    elseif ($action === 'edit' && $id === (int)$me['id'] && $role !== 'admin') $error = 'Anda tidak dapat menurunkan role akun Anda sendiri.';
    elseif ($action === 'edit' && $pel && $pel['jml_service'] > 0 && $role !== 'pelanggan') $error = 'Role tidak dapat diubah karena pengguna ini memiliki data servis sebagai pelanggan.';
    elseif ($role === 'pelanggan' && ($nama === '' || $email === '')) $error = 'Nama lengkap dan email wajib diisi untuk pelanggan.';
    elseif ($role === 'pelanggan' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Format email tidak valid.';
    elseif ($role === 'pelanggan' && db_one($conn, "SELECT id FROM pelanggan WHERE email = ? AND (user_id IS NULL OR user_id <> ?)", 'si', $email, $id)) $error = 'Email sudah dipakai pelanggan lain.';

    if ($error) {
        flash('danger', $error);
        redirect($back);
    }

    $conn->begin_transaction();
    if ($action === 'tambah') {
        db_exec($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)", 'sss', $username, password_hash($password, PASSWORD_DEFAULT), $role);
        $id = $conn->insert_id;
    } elseif ($action === 'edit') {
        db_exec($conn, "UPDATE users SET username = ?, role = ? WHERE id = ?", 'ssi', $username, $role, $id);
        if ($password !== '') {
            db_exec($conn, "UPDATE users SET password = ? WHERE id = ?", 'si', password_hash($password, PASSWORD_DEFAULT), $id);
        }
    }
    if ($role === 'pelanggan') {
        if ($pel) {
            db_exec($conn, "UPDATE pelanggan SET nama = ?, email = ?, no_hp = ? WHERE id = ?", 'sssi', $nama, $email, $no_hp, $pel['id']);
        } else {
            db_exec($conn, "INSERT INTO pelanggan (user_id, nama, email, no_hp) VALUES (?, ?, ?, ?)", 'isss', $id, $nama, $email, $no_hp);
        }
    }
    $conn->commit();
    flash('success', 'Pengguna <b>' . e($username) . '</b> berhasil ' . ($action === 'tambah' ? 'ditambahkan' : 'diperbarui') . '.');
    redirect($back);
}

// ---- Data tabel: filter role, pencarian, pagination ----
$q      = trim($_GET['q'] ?? '');
$fRole  = isset(ROLE_LABEL[$_GET['role'] ?? '']) ? $_GET['role'] : '';
$like   = '%' . $q . '%';
$from   = " FROM users u LEFT JOIN pelanggan p ON p.user_id = u.id
            WHERE (u.username LIKE ? OR p.nama LIKE ? OR p.email LIKE ? OR p.no_hp LIKE ?)" . ($fRole ? " AND u.role = '" . $fRole . "'" : '');
$total  = db_one($conn, "SELECT COUNT(*) n" . $from, 'ssss', $like, $like, $like, $like)['n'];
[$limit, $pageNo, $offset, $pages] = paginate($total);
$rows = db_all($conn, "SELECT u.id, u.username, u.role, u.created_at, p.nama, p.email, p.no_hp,
                       (SELECT COUNT(*) FROM service s WHERE s.id_pelanggan = p.id) AS jml_service" . $from
    . " ORDER BY FIELD(u.role, 'admin', 'teknisi', 'pelanggan'), u.username LIMIT $limit OFFSET $offset", 'ssss', $like, $like, $like, $like);
$count = array_column(db_all($conn, "SELECT role, COUNT(*) n FROM users GROUP BY role"), 'n', 'role');

$form = function ($u = null) {
    $isPel = ($u['role'] ?? 'pelanggan') === 'pelanggan';
    $h = '<form method="post" data-role-form>' . csrf_field()
        . '<input type="hidden" name="action" value="' . ($u ? 'edit' : 'tambah') . '"><input type="hidden" name="id" value="' . (int)($u['id'] ?? 0) . '">'
        . '<div class="modal-body">'
        . '<div class="field"><span class="label">Role</span><div class="radio-group">';
    foreach (ROLE_LABEL as $value => $label) {
        $h .= '<label class="radio"><input type="radio" name="role" value="' . $value . '"' . (($u['role'] ?? 'pelanggan') === $value ? ' checked' : '') . '><span>' . $label . '</span></label>';
    }
    return $h . '</div></div>'
        . '<div class="field-row">'
        . '<label class="field"><span class="label">Nama Pengguna</span><input class="input" name="username" value="' . e($u['username'] ?? '') . '" placeholder="Untuk masuk ke sistem" required></label>'
        . '<label class="field"><span class="label">Kata Sandi</span><input class="input" type="password" name="password" minlength="6" placeholder="' . ($u ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter') . '"' . ($u ? '' : ' required') . '></label>'
        . '</div>'
        . '<div class="col gap-16' . ($isPel ? '' : ' hidden') . '" data-pelanggan-fields>'
        .   '<label class="field"><span class="label">Nama Lengkap</span><input class="input" name="nama" value="' . e($u['nama'] ?? '') . '" placeholder="Nama pelanggan"' . ($isPel ? ' required' : '') . '></label>'
        .   '<div class="field-row">'
        .     '<label class="field"><span class="label">Email</span><input class="input" type="email" name="email" value="' . e($u['email'] ?? '') . '" placeholder="nama@email.com"' . ($isPel ? ' required' : '') . '></label>'
        .     '<label class="field"><span class="label">Nomor HP</span><input class="input" name="no_hp" value="' . e($u['no_hp'] ?? '') . '" placeholder="08xx-xxxx-xxxx"></label>'
        .   '</div>'
        . '</div>'
        . '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button>'
        . '<button type="submit" class="btn btn-primary">' . ($u ? 'Simpan Perubahan' : 'Simpan') . '</button></div></form>';
};
?>
<?= page_head('Data Pengguna', 'Kelola akun admin, teknisi, dan pelanggan dalam satu halaman.', '<button type="button" class="btn btn-primary" data-open="modalTambah">' . ic('plus', 'icon icon-sm') . '<span>Tambah Pengguna</span></button>') ?>
<?= render_flash() ?>

<div class="row gap-16 card-grid">
  <?php foreach ([['', 'Semua Pengguna', 'users', '#0d6efd'], ['admin', 'Admin', 'shield', '#0a58ca'], ['teknisi', 'Teknisi', 'wrench', '#b58100'], ['pelanggan', 'Pelanggan', 'user', '#198754']] as [$r, $label, $icon, $color]): ?>
  <a class="card grow<?= $fRole === $r ? ' card-active' : '' ?>" href="<?= e(qs(['role' => $r, 'p' => null])) ?>">
    <div class="card-body" style="flex-direction:row;align-items:center">
      <div class="stat-icon" style="background:<?= $color ?>1a;color:<?= $color ?>"><?= ic($icon) ?></div>
      <div class="col gap-4"><div class="small muted semibold"><?= $label ?></div><div class="h2"><?= (int)($r ? ($count[$r] ?? 0) : array_sum($count)) ?></div></div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <form method="get" class="card-body toolbar">
    <input type="hidden" name="page" value="pengguna">
    <div class="input-group" style="width:420px">
      <input class="input" name="q" value="<?= e($q) ?>" placeholder="Cari nama pengguna, nama, email, atau no. HP...">
      <button class="btn btn-outline-secondary" type="submit"><?= ic('search', 'icon icon-sm') ?><span>Cari</span></button>
    </div>
    <div class="row gap-12">
      <select class="input input-sm" name="role" data-autosubmit>
        <option value="">Semua Role</option>
        <?php foreach (ROLE_LABEL as $k => $label): ?><option value="<?= $k ?>"<?= $fRole === $k ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
      </select>
      <select class="input input-sm" name="limit" data-autosubmit>
        <?php foreach ([10, 20, 50, 100] as $n): ?><option value="<?= $n ?>"<?= $limit === $n ? ' selected' : '' ?>><?= $n ?> Data</option><?php endforeach; ?>
      </select>
    </div>
  </form>

  <?php if (!$rows): ?>
    <div class="empty"><?= ic('users', 'icon icon-xl') ?>Tidak ada pengguna<?= $q || $fRole ? ' yang cocok dengan filter' : '' ?>.</div>
  <?php else: ?>
  <div class="table-wrap"><div class="table" style="min-width:1050px">
    <div class="tr thead">
      <div class="th" style="width:60px">No</div><div class="th" style="width:190px">Nama Pengguna</div><div class="th" style="flex:1">Nama Lengkap</div>
      <div class="th" style="width:130px">Role</div><div class="th" style="width:220px">Email</div><div class="th" style="width:150px">No. HP</div>
      <div class="th" style="width:100px">Servis</div><div class="th" style="width:190px">Aksi</div>
    </div>
    <?php foreach ($rows as $i => $u): $self = (int)$u['id'] === (int)$me['id']; ?>
    <div class="tr">
      <div class="td" style="width:60px"><?= $offset + $i + 1 ?></div>
      <div class="td" style="width:190px"><span class="avatar"><?= e(initials($u['username'])) ?></span><span class="semibold"><?= e($u['username']) ?></span><?= $self ? '<span class="small muted">(Anda)</span>' : '' ?></div>
      <div class="td" style="flex:1"><?= e($u['nama'] ?: '-') ?></div>
      <div class="td" style="width:130px"><?= badge($u['role'], ROLE_LABEL[$u['role']]) ?></div>
      <div class="td" style="width:220px"><?= e($u['email'] ?: '-') ?></div>
      <div class="td" style="width:150px"><?= e($u['no_hp'] ?: '-') ?></div>
      <div class="td" style="width:100px"><?= $u['role'] === 'pelanggan' ? (int)$u['jml_service'] : '-' ?></div>
      <div class="td" style="width:190px">
        <button type="button" class="btn btn-warning btn-sm" data-open="modalEdit<?= (int)$u['id'] ?>"><?= ic('edit', 'icon icon-sm') ?><span>Ubah</span></button>
        <?php if (!$self): ?><?= delete_button('hapus', $u['id'], 'Akun "' . $u['username'] . '"' . ($u['nama'] ? ' (' . $u['nama'] . ')' : '') . ' akan dihapus.') ?><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>

  <div class="card-body toolbar" style="border-top:1px solid var(--border)">
    <div class="small muted">Menampilkan <?= $total ? ($offset + 1) . '–' . ($offset + count($rows)) : 0 ?> dari <?= (int)$total ?> pengguna</div>
    <?= pagination_links($pageNo, $pages) ?>
  </div>
</div>

<?= modal_open('modalTambah', 'Tambah Pengguna') . $form() . modal_close() ?>
<?php foreach ($rows as $u): ?>
  <?= modal_open('modalEdit' . (int)$u['id'], 'Ubah Pengguna') . $form($u) . modal_close() ?>
<?php endforeach; ?>
