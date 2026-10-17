<?php
$title = 'Admin — Manajemen Invoice';
$desc  = 'Tabel invoice dengan status pembayaran, aksi cetak PDF, edit & hapus.';
$inv = [
    ['Laptop Tidak Nyala', '14-10-2026', 750000, 'menunggu', 'Pending'],
    ['Ganti Layar', '11-10-2026', 1250000, 'selesai', 'Lunas'],
    ['Upgrade SSD', '06-10-2026', 850000, 'selesai', 'Lunas'],
    ['Keyboard Error', '08-10-2026', 350000, 'batal', 'Batal'],
    ['Overheat', '16-10-2026', 400000, 'menunggu', 'Pending'],
];
$rows = [];
foreach ($inv as $i => $r) {
    $rows[] = [$i + 1, '<span class="semibold">' . $r[0] . '</span>', $r[1], '<span class="semibold">' . rp($r[2]) . '</span>', badge($r[3], $r[4]), 'admin',
        '<div class="row gap-8">' . btn('Cetak', 'primary', 'printer', 'btn-sm') . btn('Edit', 'warning', 'edit', 'btn-sm') . btn('Hapus', 'danger', 'trash', 'btn-sm') . '</div>'];
}
$sum = [['Total Invoice', '5', 'receipt', '#0d6efd'], ['Lunas', rp(2100000), 'check', '#198754'], ['Pending', rp(1150000), 'clock', '#b58100']];
$cards = '';
foreach ($sum as $c) {
    $cards .= '<div class="card grow"><div class="card-body" style="flex-direction:row;align-items:center"><div class="stat-icon" style="background:' . $c[3] . '1a;color:' . $c[3] . '">' . ic($c[2]) . '</div><div class="col gap-4"><div class="small muted semibold">' . $c[0] . '</div><div class="h2">' . $c[1] . '</div></div></div></div>';
}
$content = page_head('Manajemen Invoice', 'Tagihan untuk setiap service yang telah dikerjakan.', btn('Tambah Invoice', 'success', 'plus'))
    . '<div class="row gap-20">' . $cards . '</div>'
    . '<div class="card">' . table([['No', 60], ['Service', null], ['Tanggal', 150], ['Total', 170], ['Status', 140], ['Pembuat', 120], ['Aksi', 290]], $rows) . '</div>';
echo admin_frame('invoice', $content);
