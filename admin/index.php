<?php
// Router panel admin & teknisi: admin/index.php?page=<nama>
// Login & logout memakai halaman publik (index.php?page=login / logout).
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/inc/layout.php';
require_once ROOT_PATH . '/functions/service.php';

$routes = [
    'dashboard'     => ['Dashboard', ['admin', 'teknisi']],
    'produk'        => ['Data Produk', ['admin']],
    'kategori'      => ['Kategori Produk', ['admin']],
    'users'         => ['Data User', ['admin']],
    'pelanggan'     => ['Data Pelanggan', ['admin']],
    'service'       => ['Service Management', ['admin', 'teknisi']],
    'invoice'       => ['Manajemen Invoice', ['admin']],
    'invoice_cetak' => ['Cetak Invoice', ['admin']],   // keluaran PDF, tanpa layout
];

$me = getCurrentUser($conn);
requireRole($me, ['admin', 'teknisi']);

$page = $_GET['page'] ?? 'dashboard';
if (!isset($routes[$page])) {
    flash('danger', 'Halaman tidak ditemukan.');
    redirect('admin/index.php');
}
[$title, $roles] = $routes[$page];
requireRole($me, $roles);
verify_csrf();

if ($page === 'invoice_cetak') {
    require __DIR__ . '/pages/invoice_cetak.php';
    exit;
}

// Halaman memproses form lebih dulu (pola POST → redirect), lalu dirender ke layout
ob_start();
require __DIR__ . '/pages/' . $page . '.php';
$content = ob_get_clean();

echo layout_head($title);
echo '<div class="frame">' . nav_panel($page, $me) . '<main class="admin-content">' . $content . '</main></div>';
echo layout_foot();
