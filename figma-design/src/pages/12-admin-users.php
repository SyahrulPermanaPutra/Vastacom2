<?php
$title = 'Admin — Data User';
$desc  = 'Daftar akun user beserta role (admin, teknisi, pelanggan) dengan aksi edit & hapus.';
$users = [[1, 'admin', 'admin', 'Admin', '01 Jun 2025'], [2, 'teknisi_andi', 'teknisi', 'Teknisi', '03 Jun 2025'], [3, 'teknisi_rudi', 'teknisi', 'Teknisi', '03 Jun 2025'], [4, 'budi', 'pelanggan', 'Pelanggan', '02 Okt 2026'], [5, 'ani', 'pelanggan', 'Pelanggan', '03 Okt 2026'], [6, 'dewi', 'pelanggan', 'Pelanggan', '09 Okt 2026']];
$rows = [];
foreach ($users as $u) {
    $rows[] = ['<span class="muted">' . $u[0] . '</span>', '<div class="row items-center gap-12"><div class="avatar">' . strtoupper(substr($u[1], 0, 2)) . '</div><span class="semibold">' . $u[1] . '</span>' . ($u[0] === 1 ? '<span class="small muted">(Anda)</span>' : '') . '</div>', badge($u[2], $u[3]), '<span class="muted">' . $u[4] . '</span>',
        $u[0] === 1 ? btn('Edit', 'warning', 'edit', 'btn-sm') : aksi_edit_hapus()];
}
$content = page_head('Data User', 'Kelola akun yang dapat masuk ke sistem.', btn('Tambah User', 'success', 'plus'))
    . '<div class="card">' . table([['ID', 90], ['Username', null], ['Role', 220], ['Dibuat', 200], ['Aksi', 200]], $rows) . '</div>';
echo admin_frame('users', $content);
