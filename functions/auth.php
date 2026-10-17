<?php
// Autentikasi & otorisasi. Satu halaman login (index.php?page=login) untuk semua role.

function isLoggedIn() {
    return !empty($_SESSION['user_id']) && ($_SESSION['loggedin'] ?? false) === true;
}

/** Data user yang login (id, username, role) atau null. */
function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    $user = db_one($conn, "SELECT id, username, role FROM users WHERE id = ?", 'i', $_SESSION['user_id']);
    if (!$user) {               // akun sudah dihapus
        $_SESSION = [];
        return null;
    }
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    return $user;
}

/** Halaman tujuan setelah login sesuai role. */
function homeForRole($role) {
    return in_array($role, ['admin', 'teknisi'], true) ? 'admin/index.php' : 'index.php?page=dashboard';
}

/** Hentikan akses bila user belum login / role tidak sesuai. */
function requireRole($user, array $roles) {
    if (!$user) {
        flash('danger', 'Silakan login terlebih dahulu.');
        redirect('index.php?page=login');
    }
    if (!in_array($user['role'], $roles, true)) {
        flash('danger', 'Akses ditolak! Anda tidak memiliki izin untuk membuka halaman tersebut.');
        redirect(homeForRole($user['role']));
    }
}

function loginUser($conn, $username, $password) {
    $user = db_one($conn, "SELECT id, username, password, role FROM users WHERE username = ?", 's', $username);
    if (!$user || !password_verify($password, $user['password'])) {
        return ['status' => false, 'message' => 'Username atau password salah.'];
    }
    session_regenerate_id(true);
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['loggedin'] = true;
    return ['status' => true, 'role' => $user['role']];
}

/** Daftarkan akun baru dengan role pelanggan (tabel users + pelanggan). */
function registerUserAsPelanggan($conn, $username, $password, $nama, $email, $no_hp) {
    if ($username === '' || $password === '' || $nama === '' || $email === '') {
        return ['status' => false, 'message' => 'Username, password, nama, dan email wajib diisi.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['status' => false, 'message' => 'Format email tidak valid.'];
    }
    if (strlen($password) < 6) {
        return ['status' => false, 'message' => 'Password minimal 6 karakter.'];
    }
    if (db_one($conn, "SELECT id FROM users WHERE username = ?", 's', $username)) {
        return ['status' => false, 'message' => 'Username sudah terdaftar.'];
    }
    if (db_one($conn, "SELECT id FROM pelanggan WHERE email = ?", 's', $email)) {
        return ['status' => false, 'message' => 'Email sudah terdaftar.'];
    }

    $conn->begin_transaction();
    try {
        db_exec($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, 'pelanggan')", 'ss', $username, password_hash($password, PASSWORD_DEFAULT));
        $user_id = $conn->insert_id;
        db_exec($conn, "INSERT INTO pelanggan (user_id, nama, email, no_hp) VALUES (?, ?, ?, ?)", 'isss', $user_id, $nama, $email, $no_hp);
        $conn->commit();
        return ['status' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('Registrasi gagal: ' . $e->getMessage());
        return ['status' => false, 'message' => 'Registrasi gagal. Silakan coba lagi nanti.'];
    }
}

/** Logout semua role, lalu kembali ke halaman login publik. */
function logoutUser() {
    // Kosongkan data login dan ganti ID sesi (sesi lama dihapus), sisakan pesan flash
    $_SESSION = [];
    session_regenerate_id(true);
    flash('success', 'Anda berhasil logout.');
    redirect('index.php?page=login');
}
