<?php
// Router area publik & pelanggan: index.php?page=home|login|register|dashboard|logout
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/inc/layout.php';

$routes = [
    'home'      => 'Service & Jual Beli Laptop',
    'login'     => 'Login',
    'register'  => 'Registrasi',
    'dashboard' => 'Dashboard Pelanggan',
];

$page = $_GET['page'] ?? 'home';
$me   = getCurrentUser($conn);

if ($page === 'logout') {
    logoutUser();
}
if (!isset($routes[$page])) {
    flash('danger', 'Halaman tidak ditemukan.');
    redirect('');
}
// User yang sudah login tidak perlu membuka login/registrasi lagi
if (in_array($page, ['login', 'register'], true) && $me) {
    redirect(homeForRole($me['role']));
}
if ($page === 'dashboard') {
    requireRole($me, ['pelanggan']);
}
verify_csrf();

// Halaman memproses form lebih dulu (boleh redirect), baru kemudian dirender ke dalam layout
ob_start();
require __DIR__ . '/pages/' . $page . '.php';
$content = ob_get_clean();

echo layout_head($routes[$page]);
echo '<div class="frame' . ($page === 'home' ? ' frame-white' : '') . '">' . nav_public($page, $me) . $content . '</div>';
echo layout_foot();
