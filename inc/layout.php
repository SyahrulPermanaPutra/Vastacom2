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

/** Tombol menu (hamburger) untuk layar kecil. */
function nav_toggle() {
    return '<button type="button" class="nav-toggle" data-nav-toggle aria-label="Buka menu">' . ic('menu') . '</button>';
}

/** Navbar area publik & pelanggan (lihat Figma 01–04). */
function nav_public($page, $me) {
    $link = function ($label, $href, $key) use ($page) {
        return '<a class="nav-link' . ($page === $key ? ' active' : '') . '" href="' . e(url($href)) . '">' . $label . '</a>';
    };
    $logout = '<a class="nav-link" href="' . e(url('index.php?page=logout')) . '">' . ic('logout', 'icon icon-sm') . '<span>Keluar</span></a>';
    $h = '<header class="navbar">' . brand_html(url()) . nav_toggle() . '<div class="nav-collapse"><nav class="nav-links">';
    if (!$me) {
        $h .= $link('Beranda', '', 'home') . $link('Masuk', 'index.php?page=login', 'login');
    } elseif ($me['role'] === 'pelanggan') {
        if ($page !== 'dashboard') $h .= $link('Beranda', '', 'home');
        $h .= $link('Dasbor', 'index.php?page=dashboard', 'dashboard') . nav_user_chip($me['username'], 'Halo, ' . $me['username'] . '!') . $logout;
    } else {
        $h .= $link('Beranda', '', 'home') . '<a class="nav-link" href="' . e(url('admin/index.php')) . '">' . ic('grid', 'icon icon-sm') . '<span>Panel ' . e(ROLE_LABEL[$me['role']]) . '</span></a>'
            . nav_user_chip($me['username']) . $logout;
    }
    return $h . '</nav></div></header>';
}

/** Navbar panel admin/teknisi dengan submenu. Menu menyesuaikan role. */
function nav_panel($page, $me) {
    // [label, ikon, role, submenu] — submenu: key => [label, ikon, role]
    $menu = [
        'dashboard' => ['Dasbor', 'grid', ['admin', 'teknisi']],
        'master'    => ['Master Data', 'package', ['admin'], [
            'kategori' => ['Kategori Produk', 'tag', ['admin']],
            'produk'   => ['Produk', 'package', ['admin']],
        ]],
        'pengguna'  => ['Pengguna', 'users', ['admin']],
        'transaksi' => ['Transaksi', 'receipt', ['admin', 'teknisi'], [
            'service' => ['Servis', 'wrench', ['admin', 'teknisi']],
            'invoice' => ['Invoice', 'receipt', ['admin']],
        ]],
    ];
    $allowed = function ($roles) use ($me) { return in_array($me['role'], $roles, true); };
    $link = function ($key, $label, $icon, $class = 'nav-link') use ($page) {
        return '<a class="' . $class . ($page === $key ? ' active' : '') . '" href="' . e(url('admin/index.php?page=' . $key)) . '">' . ic($icon, 'icon icon-sm') . '<span>' . $label . '</span></a>';
    };

    $h = '<header class="navbar navbar-admin">' . brand_html(url('admin/index.php')) . nav_toggle() . '<div class="nav-collapse"><nav class="nav-links">';
    foreach ($menu as $key => $item) {
        [$label, $icon, $roles] = $item;
        if (!$allowed($roles)) continue;
        if (empty($item[3])) {
            $h .= $link($key, $label, $icon);
            continue;
        }
        $subs = array_filter($item[3], function ($s) use ($allowed) { return $allowed($s[2]); });
        if (count($subs) === 1) {           // hanya satu submenu (mis. teknisi) → tampil sebagai tautan biasa
            $sk = array_key_first($subs);
            $h .= $link($sk, $subs[$sk][0], $subs[$sk][1]);
            continue;
        }
        $active = isset($subs[$page]);
        $h .= '<div class="nav-group"><button type="button" class="nav-link' . ($active ? ' active' : '') . '" data-dropdown aria-haspopup="true">'
            . ic($icon, 'icon icon-sm') . '<span>' . $label . '</span>' . ic('down', 'icon icon-sm nav-caret') . '</button><div class="nav-menu">';
        foreach ($subs as $sk => [$sl, $si]) {
            $h .= $link($sk, $sl, $si, 'nav-menu-item');
        }
        $h .= '</div></div>';
    }
    $h .= '</nav><div class="nav-right">'
        . '<a class="nav-link" href="' . e(url()) . '" title="Lihat situs">' . ic('laptop', 'icon icon-sm') . '<span>Lihat Situs</span></a>'
        . nav_user_chip($me['username'])
        . '<a class="nav-link" href="' . e(url('index.php?page=logout')) . '">' . ic('logout', 'icon icon-sm') . '<span>Keluar</span></a></div>';
    return $h . '</div></header>';
}

function page_head($title, $sub, $actions = '') {
    return '<div class="page-head"><div class="col gap-4"><h1 class="h1">' . $title . '</h1><div class="body">' . $sub . '</div></div><div class="row gap-12">' . $actions . '</div></div>';
}
