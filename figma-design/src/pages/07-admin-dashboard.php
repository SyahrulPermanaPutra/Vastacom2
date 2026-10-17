<?php
$title = 'Admin — Dashboard';
$desc  = 'Kartu statistik, service terbaru, grafik status service & invoice terbaru.';

$stats = [
    ['Total Service', '128+', 'wrench', 'bg-primary'],
    ['Pelanggan', '85+', 'users', 'bg-success'],
    ['Invoice Pending', '8', 'receipt', 'bg-warning'],
    ['Pendapatan Bulan Ini', 'Rp12.450.000', 'wallet', 'bg-info'],
];
$svc = [];
foreach (array_slice($SERVICES, 0, 4) as $i => $s) {
    $svc[] = ['<span class="semibold">#SVC-' . (125 - $i) . '</span>', $s[0], $s[1], badge($s[3], $STATUS_LABEL[$s[3]])];
}
$inv = [
    ['#INV-2026-125', '#SVC-120', 'Dewi Lestari', rp(1250000), badge('selesai', 'Lunas')],
    ['#INV-2026-124', '#SVC-119', 'Agus Pratama', rp(850000), badge('menunggu', 'Pending')],
    ['#INV-2026-123', '#SVC-118', 'Budi Santoso', rp(750000), badge('menunggu', 'Pending')],
    ['#INV-2026-122', '#SVC-117', 'Siti Rahayu', rp(350000), badge('batal', 'Batal')],
];
foreach ($inv as &$r) $r[] = '<div class="row gap-8">' . btn('', 'outline-primary', 'eye', 'btn-sm btn-icon') . btn('', 'outline-secondary', 'printer', 'btn-sm btn-icon') . '</div>';
unset($r);

// Donut chart (C = 2πr, r = 70)
$C = 2 * M_PI * 70;
$seg = [['Selesai', 45, '#198754'], ['Diproses', 25, '#0dcaf0'], ['Menunggu', 20, '#ffc107'], ['Dibatalkan', 10, '#dc3545']];
$donut = '<svg width="220" height="220" viewBox="0 0 220 220"><g transform="rotate(-90 110 110)">';
$off = 0;
foreach ($seg as [$l, $pct, $col]) {
    $len = $C * $pct / 100;
    $donut .= '<circle cx="110" cy="110" r="70" fill="none" stroke="' . $col . '" stroke-width="34" stroke-dasharray="' . round($len, 2) . ' ' . round($C - $len, 2) . '" stroke-dashoffset="' . round(-$off, 2) . '"/>';
    $off += $len;
}
$donut .= '</g><text x="110" y="106" text-anchor="middle" font-family="Inter" font-size="28" font-weight="800" fill="#212529">128</text><text x="110" y="128" text-anchor="middle" font-family="Inter" font-size="12" fill="#64748b">Total Service</text></svg>';
$legend = '';
foreach ($seg as [$l, $pct, $col]) $legend .= '<div class="legend"><div class="legend-dot" style="background:' . $col . '"></div><span class="grow">' . $l . '</span><span class="semibold">' . $pct . '%</span></div>';

$content = page_head('Dashboard', 'Selamat datang di halaman dashboard <b>admin</b>');
$cards = '';
foreach ($stats as $s) {
    $cards .= '<div class="stat-card ' . $s[3] . '"><div class="row between items-center"><div class="stat-label">' . $s[0] . '</div><div class="stat-icon">' . ic($s[2], 'icon icon-lg') . '</div></div><div class="stat-value">' . $s[1] . '</div></div>';
}
$content .= '<div class="row gap-20">' . $cards . '</div>';
$content .= '<div class="row gap-20 items-start">'
    . '<div class="card grow"><div class="card-header"><div class="h4">Service Terbaru</div>' . btn('Lihat Semua', 'outline-primary', null, 'btn-sm') . '</div>'
    . table([['ID', 110], ['Pelanggan', 150], ['Jenis Kerusakan', null], ['Status', 130]], $svc) . '</div>'
    . '<div class="card" style="width:480px"><div class="card-header"><div class="h4">Statistik Service</div></div><div class="card-body" style="flex-direction:row;align-items:center;gap:28px">' . $donut . '<div class="col gap-12 grow">' . $legend . '</div></div></div>'
    . '</div>';
$content .= '<div class="card"><div class="card-header"><div class="h4">Invoice Terbaru</div>' . btn('Lihat Semua', 'outline-primary', null, 'btn-sm') . '</div>'
    . table([['No. Invoice', 200], ['Service ID', 150], ['Pelanggan', null], ['Total Biaya', 200], ['Status', 150], ['Aksi', 120]], $inv) . '</div>';

echo admin_frame('dashboard', $content);
