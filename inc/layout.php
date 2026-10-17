<?php
// Potongan layout yang dipakai area publik (index.php) dan panel (admin/index.php).

function layout_head($title) {
    $v = '?v=' . (@filemtime(ROOT_PATH . '/assets/css/app.css') ?: 1);
    return '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<title>' . e($title) . ' | Vasta Computer</title>'
        . '<link rel="preconnect" href="https://fonts.googleapis.com">'
        . '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">'
        . '<link rel="stylesheet" href="' . url('assets/css/design.css') . $v . '">'
        . '<link rel="stylesheet" href="' . url('assets/css/app.css') . $v . '">'
        . '</head><body>';
}

function layout_foot() {
    $v = '?v=' . (@filemtime(ROOT_PATH . '/assets/js/app.js') ?: 1);
    return modal_open('confirmModal', 'Konfirmasi Hapus', 'modal-sm')
        . '<div class="modal-body"><div class="row gap-16 items-start"><div class="stat-icon" style="background:#f8d7da;color:#dc3545">' . ic('trash', 'icon icon-lg') . '</div>'
        . '<div class="col gap-4 grow"><div class="h4">Yakin ingin menghapus?</div><div class="body" data-confirm-text></div></div></div></div>'
        . '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Batal</button><button type="button" class="btn btn-danger" data-confirm-ok>' . ic('trash', 'icon icon-sm') . '<span>Hapus</span></button></div>'
        . modal_close()
        . '<script src="' . url('assets/js/app.js') . $v . '"></script></body></html>';
}

function brand_html($href) {
    return '<a class="brand" href="' . e($href) . '"><span class="brand-logo">' . ic('laptop') . '</span><span>Vasta Computer</span></a>';
}

function nav_user_chip($username, $label = null) {
    return '<span class="nav-user"><span class="avatar">' . e(initials($username)) . '</span><span>' . e($label ?? $username) . '</span></span>';
}

/** Navbar area publik & pelanggan (lihat Figma 01–04). */
function nav_public($page, $me) {
    $link = function ($label, $href, $key) use ($page) {
        return '<a class="nav-link' . ($page === $key ? ' active' : '') . '" href="' . e(url($href)) . '">' . $label . '</a>';
    };
    $logout = '<a class="nav-link" href="' . e(url('index.php?page=logout')) . '">' . ic('logout', 'icon icon-sm') . '<span>Logout</span></a>';
    $h = '<header class="navbar">' . brand_html(url()) . '<nav class="nav-links">';
    if (!$me) {
        $h .= $link('Home', '', 'home') . $link('Registrasi', 'index.php?page=register', 'register') . $link('Login', 'index.php?page=login', 'login');
    } elseif ($me['role'] === 'pelanggan') {
        if ($page !== 'dashboard') $h .= $link('Home', '', 'home');
        $h .= $link('Dashboard', 'index.php?page=dashboard', 'dashboard') . nav_user_chip($me['username'], 'Halo, ' . $me['username'] . '!') . $logout;
    } else {
        $h .= $link('Home', '', 'home') . '<a class="nav-link" href="' . e(url('admin/index.php')) . '">' . ic('grid', 'icon icon-sm') . '<span>Panel ' . e(ROLE_LABEL[$me['role']]) . '</span></a>'
            . nav_user_chip($me['username']) . $logout;
    }
    return $h . '</nav></header>';
}

/** Navbar panel admin/teknisi (lihat Figma 07–21). Menu menyesuaikan role. */
function nav_panel($page, $me) {
    $items = [
        'dashboard' => ['Dashboard', 'grid', ['admin', 'teknisi']],
        'produk'    => ['Produk', 'package', ['admin']],
        'kategori'  => ['Kategori Produk', 'tag', ['admin']],
        'users'     => ['Users', 'user', ['admin']],
        'pelanggan' => ['Pelanggan', 'users', ['admin']],
        'service'   => ['Service', 'wrench', ['admin', 'teknisi']],
        'invoice'   => ['Invoice', 'receipt', ['admin']],
    ];
    $h = '<header class="navbar navbar-admin">' . brand_html(url('admin/index.php')) . '<nav class="nav-links">';
    foreach ($items as $key => [$label, $icon, $roles]) {
        if (!in_array($me['role'], $roles, true)) continue;
        $h .= '<a class="nav-link' . ($page === $key ? ' active' : '') . '" href="' . e(url('admin/index.php?page=' . $key)) . '">' . ic($icon, 'icon icon-sm') . '<span>' . $label . '</span></a>';
    }
    $h .= '</nav><div class="row items-center gap-8">'
        . '<a class="nav-link" href="' . e(url()) . '" title="Lihat website">' . ic('laptop', 'icon icon-sm') . '<span>Website</span></a>'
        . nav_user_chip($me['username'])
        . '<a class="nav-link" href="' . e(url('index.php?page=logout')) . '">' . ic('logout', 'icon icon-sm') . '<span>Keluar</span></a></div>';
    return $h . '</header>';
}

function page_head($title, $sub, $actions = '') {
    return '<div class="page-head"><div class="col gap-4"><h1 class="h1">' . $title . '</h1><div class="body">' . $sub . '</div></div><div class="row gap-12">' . $actions . '</div></div>';
}
