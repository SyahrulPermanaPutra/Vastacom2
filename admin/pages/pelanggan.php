<?php
// Data Pelanggan (Figma: 14 & 15). Setiap pelanggan memiliki akun login (role pelanggan).
const DEFAULT_PASSWORD_PELANGGAN = '123456';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $no_hp  = trim($_POST['no_hp'] ?? '');

    if ($action === 'hapus') {
        $p = db_one($conn, "SELECT p.*, (SELECT COUNT(*) FROM service s WHERE s.id_pelanggan = p.id) AS jml_service FROM pelanggan p WHERE p.id = ?", 'i', $id);
        if (!$p) {
            flash('danger', 'Pelanggan tidak ditemukan.');
        } elseif ($p['jml_service'] > 0) {
            flash('danger', 'Pelanggan <b>' . e($p['nama']) . '</b> tidak dapat dihapus karena memiliki ' . (int)$p['jml_service'] . ' data service.');
        } else {
            $conn->begin_transaction();
            db_exec($conn, "DELETE FROM pelanggan WHERE id = ?", 'i', $id);
            if ($p['user_id']) db_exec($conn, "DELETE FROM users WHERE id = ? AND role = 'pelanggan'", 'i', $p['user_id']);
            $conn->commit();
            flash('success', 'Pelanggan berhasil <b>dihapus</b>.');
        }
        redirect('admin/index.php' . qs());
    }

    $error = null;
    if ($nama === '' || $email === '') $error = 'Nama dan email wajib diisi.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Format email tidak valid.';
    elseif (db_one($conn, "SELECT id FROM pelanggan WHERE email = ? AND id <> ?", 'si', $email, $id)) $error = 'Email sudah digunakan pelanggan lain.';

    if ($error) {
        flash('danger', $error);
    } elseif ($action === 'tambah') {
        // Buat akun login otomatis: username dari nama (unik) dengan password default
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $nama)) ?: 'pelanggan';
        $username = $base;
        for ($n = 2; db_one($conn, "SELECT id FROM users WHERE username = ?", 's', $username); $n++) $username = $base . $n;

        $conn->begin_transaction();
        db_exec($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, 'pelanggan')", 'ss', $username, password_hash(DEFAULT_PASSWORD_PELANGGAN, PASSWORD_DEFAULT));
        db_exec($conn, "INSERT INTO pelanggan (user_id, nama, email, no_hp) VALUES (?, ?, ?, ?)", 'isss', $conn->insert_id, $nama, $email, $no_hp);
        $conn->commit();
        flash('success', 'Pelanggan berhasil ditambahkan. Akun login: <b>' . e($username) . '</b> / password <b>' . DEFAULT_PASSWORD_PELANGGAN . '</b>');
    } elseif ($action === 'edit') {
        db_exec($conn, "UPDATE pelanggan SET nama = ?, email = ?, no_hp = ? WHERE id = ?", 'sssi', $nama, $email, $no_hp, $id);
        flash('success', 'Pelanggan berhasil <b>diperbarui</b>.');
    }
    redirect('admin/index.php' . qs());
}

$q     = trim($_GET['q'] ?? '');
$like  = '%' . $q . '%';
$where = " WHERE p.nama LIKE ? OR p.email LIKE ? OR p.no_hp LIKE ? OR u.username LIKE ?";
$total = db_one($conn, "SELECT COUNT(*) n FROM pelanggan p LEFT JOIN users u ON p.user_id = u.id" . $where, 'ssss', $like, $like, $like, $like)['n'];
[$limit, $pageNo, $offset, $pages] = paginate($total);
$rows = db_all($conn, "SELECT p.*, u.username FROM pelanggan p LEFT JOIN users u ON p.user_id = u.id" . $where
    . " ORDER BY p.id DESC LIMIT $limit OFFSET $offset", 'ssss', $like, $like, $like, $like);

$form = function ($p = null) {
    return '<form method="post">' . csrf_field()
        . '<input type="hidden" name="action" value="' . ($p ? 'edit' : 'tambah') . '"><input type="hidden" name="id" value="' . (int)($p['id'] ?? 0) . '">'
        . '<div class="modal-body">'
        . '<label class="field"><span class="label">Nama Lengkap</span><input class="input" name="nama" value="' . e($p['nama'] ?? '') . '" placeholder="Nama Pelanggan" required></label>'
        . '<label class="field"><span class="label">Email</span><input class="input" type="email" name="email" value="' . e($p['email'] ?? '') . '" placeholder="Email Pelanggan" required></label>'
        . '<label class="field"><span class="label">Nomor HP</span><input class="input" name="no_hp" value="' . e($p['no_hp'] ?? '') . '" placeholder="Nomor HP"></label>'
        . ($p ? '' : '<div class="alert alert-info">' . ic('user') . '<span>Akun login dibuat otomatis dengan password default <b>' . DEFAULT_PASSWORD_PELANGGAN . '</b>.</span></div>')
        . '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button>'
        . '<button type="submit" class="btn btn-primary">' . ($p ? 'Simpan Perubahan' : 'Simpan') . '</button></div></form>';
};
?>
<?= page_head('Data Pelanggan', 'Daftar pelanggan yang terdaftar di Vasta Computer.', '<button type="button" class="btn btn-primary" data-open="modalTambah">' . ic('plus', 'icon icon-sm') . '<span>Tambah Pelanggan</span></button>') ?>
<?= render_flash() ?>

<div class="card">
  <form method="get" class="card-body toolbar">
    <input type="hidden" name="page" value="pelanggan">
    <div class="input-group" style="width:420px">
      <input class="input" name="q" value="<?= e($q) ?>" placeholder="Cari pelanggan...">
      <button class="btn btn-outline-secondary" type="submit"><?= ic('search', 'icon icon-sm') ?><span>Cari</span></button>
    </div>
    <select class="input input-sm" name="limit" data-autosubmit>
      <?php foreach ([10, 20, 50, 100] as $n): ?><option value="<?= $n ?>"<?= $limit === $n ? ' selected' : '' ?>><?= $n ?> Data</option><?php endforeach; ?>
    </select>
  </form>

  <?php if (!$rows): ?>
    <div class="empty"><?= ic('users', 'icon icon-xl') ?>Tidak ada pelanggan<?= $q ? ' yang cocok dengan pencarian' : '' ?>.</div>
  <?php else: ?>
  <div class="table-wrap"><div class="table">
    <div class="tr thead">
      <div class="th" style="width:60px">No</div><div class="th" style="flex:1">Nama</div><div class="th" style="width:240px">Email</div><div class="th" style="width:140px">Username</div>
      <div class="th" style="width:160px">No. HP</div><div class="th" style="width:150px">Dibuat Pada</div><div class="th" style="width:200px">Aksi</div>
    </div>
    <?php foreach ($rows as $i => $p): ?>
    <div class="tr">
      <div class="td" style="width:60px"><?= $offset + $i + 1 ?></div>
      <div class="td semibold" style="flex:1"><?= e($p['nama']) ?></div>
      <div class="td" style="width:240px"><?= e($p['email'] ?: '-') ?></div>
      <div class="td" style="width:140px"><?= e($p['username'] ?: '-') ?></div>
      <div class="td" style="width:160px"><?= e($p['no_hp'] ?: '-') ?></div>
      <div class="td muted" style="width:150px"><?= tgl($p['created_at'], false) ?></div>
      <div class="td" style="width:200px">
        <button type="button" class="btn btn-warning btn-sm" data-open="modalEdit<?= (int)$p['id'] ?>"><?= ic('edit', 'icon icon-sm') ?><span>Edit</span></button>
        <?= delete_button('hapus', $p['id'], 'Pelanggan "' . $p['nama'] . '" beserta akun loginnya akan dihapus.') ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>

  <div class="card-body toolbar" style="border-top:1px solid var(--border)">
    <div class="small muted">Menampilkan <?= $total ? ($offset + 1) . '–' . ($offset + count($rows)) : 0 ?> dari <?= (int)$total ?> pelanggan</div>
    <?= pagination_links($pageNo, $pages) ?>
  </div>
</div>

<?= modal_open('modalTambah', 'Tambah Pelanggan') . $form() . modal_close() ?>
<?php foreach ($rows as $p): ?>
  <?= modal_open('modalEdit' . (int)$p['id'], 'Edit Pelanggan') . $form($p) . modal_close() ?>
<?php endforeach; ?>
