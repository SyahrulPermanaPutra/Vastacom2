<?php
// Helper untuk menyusun halaman statis desain Figma.
// Dipakai oleh build.php — hasil akhirnya HTML murni tanpa PHP/JS.

require_once __DIR__ . "/../../functions/ui.php"; // ic() — ikon dipakai bersama dengan aplikasi

function btn($text, $variant = 'primary', $icon = null, $extra = '') {
    return '<div class="btn btn-' . $variant . ' ' . $extra . '">' . ($icon ? ic($icon, 'icon icon-sm') : '') . ($text !== '' ? '<span>' . $text . '</span>' : '') . '</div>';
}

function badge($type, $text) {
    return '<div class="badge badge-' . $type . '"><div class="badge-dot"></div><span>' . $text . '</span></div>';
}

function field($label, $control, $help = '') {
    return '<div class="field"><div class="label">' . $label . '</div>' . $control . ($help ? '<div class="help">' . $help . '</div>' : '') . '</div>';
}

function input($value, $placeholder = false, $extra = '') {
    return '<div class="input ' . ($placeholder ? 'placeholder ' : '') . $extra . '"><span>' . $value . '</span></div>';
}

function select($value, $placeholder = false, $extra = '') {
    return '<div class="input ' . ($placeholder ? 'placeholder ' : '') . $extra . '"><span>' . $value . '</span>' . ic('down', 'icon icon-sm') . '</div>';
}

function textarea($value, $placeholder = false) {
    return '<div class="input textarea ' . ($placeholder ? 'placeholder' : '') . '"><span>' . $value . '</span></div>';
}

function file_input($name = 'Tidak ada file dipilih') {
    return '<div class="file"><div class="file-btn">Pilih File</div><div class="file-name">' . $name . '</div></div>';
}

function radios($labels, $checked = -1) {
    $h = '<div class="radio-group">';
    foreach ($labels as $i => $l) {
        $h .= '<div class="radio"><div class="radio-dot' . ($i === $checked ? ' checked' : '') . '"><div class="radio-inner"></div></div><span>' . $l . '</span></div>';
    }
    return $h . '</div>';
}

// $cols: [[label, width_px|null(grow)], ...], $rows: [[cell, cell, ...], ...]
function table($cols, $rows, $extra = '') {
    $style = function ($w) { return $w ? 'style="width:' . $w . 'px;flex-shrink:0"' : 'style="flex:1"'; };
    $h = '<div class="table ' . $extra . '"><div class="tr thead">';
    foreach ($cols as $c) $h .= '<div class="th" ' . $style($c[1]) . '>' . $c[0] . '</div>';
    $h .= '</div>';
    foreach ($rows as $r) {
        $h .= '<div class="tr">';
        foreach ($r as $i => $cell) $h .= '<div class="td" ' . $style($cols[$i][1]) . '>' . $cell . '</div>';
        $h .= '</div>';
    }
    return $h . '</div>';
}

// Tabel key-value (detail)
function kv($pairs) {
    $h = '<div class="table table-bordered">';
    foreach ($pairs as $k => $v) $h .= '<div class="kv"><div class="kv-key">' . $k . '</div><div class="kv-val">' . $v . '</div></div>';
    return $h . '</div>';
}

function modal($title, $body, $footer, $size = '', $headerExtra = '') {
    return '<div class="modal ' . $size . '">'
        . '<div class="modal-header ' . $headerExtra . '"><div class="modal-title">' . $title . '</div><div class="modal-close">' . ic('x') . '</div></div>'
        . '<div class="modal-body">' . $body . '</div>'
        . '<div class="modal-footer">' . $footer . '</div></div>';
}

function overlay(...$modals) {
    return '<div class="overlay">' . implode('', $modals) . '</div>';
}

function pagination($pages = 3, $active = 1) {
    $h = '<div class="pagination"><div class="page' . ($active == 1 ? ' disabled' : '') . '">' . ic('left', 'icon icon-sm') . '</div>';
    for ($i = 1; $i <= $pages; $i++) $h .= '<div class="page' . ($i == $active ? ' active' : '') . '">' . $i . '</div>';
    return $h . '<div class="page">' . ic('right', 'icon icon-sm') . '</div></div>';
}

function brand() {
    return '<div class="brand"><div class="brand-logo">' . ic('laptop', 'icon') . '</div><span>Vasta Computer</span></div>';
}

// $state: 'guest' | 'customer' | 'dashboard'
function nav_public($active = 'home', $state = 'guest') {
    $link = function ($label, $key) use ($active) {
        return '<div class="nav-link' . ($key === $active ? ' active' : '') . '">' . $label . '</div>';
    };
    $h = '<div class="navbar">' . brand() . '<div class="nav-links">';
    if ($state === 'dashboard') {
        $h .= '<div class="nav-user"><div class="avatar">BS</div><span>Halo, budi!</span></div>';
        $h .= '<div class="nav-link">' . ic('logout', 'icon icon-sm') . '<span>Logout</span></div>';
    } elseif ($state === 'customer') {
        $h .= $link('Home', 'home') . $link('Dashboard', 'dashboard') . '<div class="nav-link">Logout</div>';
        $h .= '<div class="nav-user"><div class="avatar">BS</div><span>Halo, budi!</span></div>';
    } else {
        $h .= $link('Home', 'home') . $link('Registrasi', 'register') . $link('Login', 'login');
    }
    return $h . '</div></div>';
}

function nav_admin($active = 'dashboard') {
    $items = [
        'dashboard' => ['Dashboard', 'grid'],
        'produk'    => ['Produk', 'package'],
        'kategori'  => ['Kategori Produk', 'tag'],
        'users'     => ['Users', 'user'],
        'pelanggan' => ['Pelanggan', 'users'],
        'service'   => ['Service', 'wrench'],
        'invoice'   => ['Invoice', 'receipt'],
    ];
    $h = '<div class="navbar navbar-admin">' . brand() . '<div class="nav-links">';
    foreach ($items as $k => [$label, $icon]) {
        $h .= '<div class="nav-link' . ($k === $active ? ' active' : '') . '">' . ic($icon, 'icon icon-sm') . '<span>' . $label . '</span></div>';
    }
    $h .= '</div><div class="row items-center gap-12"><div class="nav-link">' . ic('laptop', 'icon icon-sm') . '<span>Website</span></div><div class="nav-user"><div class="avatar">AD</div><span>admin</span></div>'
        . '<div class="nav-link">' . ic('logout', 'icon icon-sm') . '<span>Keluar</span></div></div>';
    return $h . '</div>';
}

function admin_frame($active, $content) {
    return '<div class="frame" style="min-height:900px">' . nav_admin($active) . '<div class="admin-content">' . $content . '</div></div>';
}

function page_head($title, $sub, $actions = '') {
    return '<div class="page-head"><div class="col gap-4"><div class="h1">' . $title . '</div><div class="body">' . $sub . '</div></div><div class="row gap-12">' . $actions . '</div></div>';
}

function aksi_edit_hapus() {
    return '<div class="row gap-8">' . btn('Edit', 'warning', 'edit', 'btn-sm') . btn('Hapus', 'danger', 'trash', 'btn-sm') . '</div>';
}

function product_img($h = '') {
    return '<div class="product-img"' . ($h ? ' style="height:' . $h . 'px"' : '') . '>' . ic('laptop', 'icon icon-xl') . '</div>';
}

function thumb() {
    return '<div class="thumb">' . ic('image', 'icon') . '</div>';
}

function rp($n) { return 'Rp' . number_format($n, 0, ',', '.'); }

// Data contoh yang dipakai berulang
$PRODUK = [
    ['Lenovo Ideapad Gaming', 13899000, 'Laptop', 1],
    ['Acer Nitro 5', 11000000, 'Laptop', 1],
    ['HP Victus 15', 9680000, 'Laptop', 1],
    ['Dell Latitude 5420', 8999000, 'Laptop', 1],
    ['Dell Latitude R', 3450000, 'Laptop', 0],
    ['SSD Samsung 970 EVO 500GB', 1150000, 'Hardware', 1],
    ['RAM Kingston DDR4 8GB', 450000, 'Hardware', 1],
    ['Keyboard Logitech K380', 525000, 'Aksesoris', 1],
];
$SERVICES = [
    ['Budi Santoso', 'Laptop Tidak Nyala', 'Laptop mati total setelah terkena air, indikator charger tidak menyala.', 'diproses', '12 Okt 2026 09:15', 'teknisi_andi'],
    ['Ani Wijaya', 'Ganti Layar', 'Layar retak di bagian pojok kanan bawah, muncul garis vertikal.', 'selesai', '10 Okt 2026 13:40', 'teknisi_rudi'],
    ['Rudi Hartono', 'Instalasi Software', 'Install ulang Windows 11 dan Microsoft Office.', 'menunggu', '14 Okt 2026 10:05', 'N/A'],
    ['Siti Rahayu', 'Keyboard Error', 'Beberapa tombol keyboard tidak berfungsi (A, S, D).', 'batal', '08 Okt 2026 16:20', 'teknisi_andi'],
    ['Dewi Lestari', 'Overheat', 'Laptop cepat panas dan mati sendiri saat bermain game.', 'diproses', '15 Okt 2026 11:30', 'teknisi_rudi'],
    ['Agus Pratama', 'Upgrade SSD', 'Upgrade HDD 1TB ke SSD 512GB + migrasi data.', 'selesai', '05 Okt 2026 08:50', 'teknisi_andi'],
];
$STATUS_LABEL = ['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'];
