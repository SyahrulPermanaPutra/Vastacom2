<?php
// Dashboard panel (Figma: 07). Admin melihat ringkasan seluruh toko, teknisi hanya tugasnya sendiri.
$isAdmin = $me['role'] === 'admin';
$scope   = $isAdmin ? '' : ' WHERE s.id_teknisi = ' . (int)$me['id'];

$statusCount = array_fill_keys(array_keys(STATUS_SERVICE), 0);
foreach (db_all($conn, "SELECT s.status, COUNT(*) n FROM service s" . $scope . " GROUP BY s.status") as $r) {
    $statusCount[$r['status']] = (int)$r['n'];
}
$totalService = array_sum($statusCount);

if ($isAdmin) {
    $pendapatan = db_one($conn, "SELECT COALESCE(SUM(total_biaya), 0) n FROM invoice WHERE status_pembayaran = 'lunas'
                                 AND DATE_FORMAT(COALESCE(tanggal_pembayaran, tanggal_invoice), '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')")['n'];
    $stats = [
        ['Total Servis', $totalService, 'wrench', 'bg-primary'],
        ['Pelanggan', db_one($conn, "SELECT COUNT(*) n FROM pelanggan")['n'], 'users', 'bg-success'],
        ['Invoice Belum Lunas', db_one($conn, "SELECT COUNT(*) n FROM invoice WHERE status_pembayaran = 'pending'")['n'], 'receipt', 'bg-warning'],
        ['Pendapatan Bulan Ini', rp($pendapatan), 'wallet', 'bg-info'],
    ];
    $invoices = db_all($conn, "SELECT i.*, p.nama AS nama_pelanggan FROM invoice i JOIN service s ON i.service_id = s.id
                               JOIN pelanggan p ON s.id_pelanggan = p.id ORDER BY i.tanggal_invoice DESC LIMIT 5");
} else {
    $stats = [
        ['Tugas Saya', $totalService, 'wrench', 'bg-primary'],
        ['Menunggu', $statusCount['menunggu'], 'clock', 'bg-warning'],
        ['Diproses', $statusCount['diproses'], 'cpu', 'bg-info'],
        ['Selesai', $statusCount['selesai'], 'check', 'bg-success'],
    ];
}
$recent = db_all($conn, SERVICE_SELECT . $scope . " ORDER BY s.tanggal_masuk DESC LIMIT 5");

// Donut chart status service (SVG, tanpa library)
$colors = ['selesai' => '#198754', 'diproses' => '#0dcaf0', 'menunggu' => '#ffc107', 'dibatalkan' => '#dc3545'];
$C = 2 * M_PI * 70;
$donut = '<svg width="200" height="200" viewBox="0 0 220 220" role="img" aria-label="Statistik status servis"><g transform="rotate(-90 110 110)">'
       . '<circle cx="110" cy="110" r="70" fill="none" stroke="#e2e8f0" stroke-width="34"/>';
$offset = 0;
$legend = '';
foreach ($colors as $status => $color) {
    $pct = $totalService ? $statusCount[$status] / $totalService : 0;
    if ($pct > 0) {
        $len = $C * $pct;
        $donut .= '<circle cx="110" cy="110" r="70" fill="none" stroke="' . $color . '" stroke-width="34" stroke-dasharray="' . round($len, 2) . ' ' . round($C - $len, 2) . '" stroke-dashoffset="' . round(-$offset, 2) . '"/>';
        $offset += $len;
    }
    $legend .= '<div class="legend"><div class="legend-dot" style="background:' . $color . '"></div><span class="grow">' . STATUS_SERVICE[$status][0]
             . '</span><span class="semibold">' . $statusCount[$status] . ' · ' . round($pct * 100) . '%</span></div>';
}
$donut .= '</g><text x="110" y="108" text-anchor="middle" font-family="Inter, sans-serif" font-size="30" font-weight="800" fill="#212529">' . $totalService . '</text>'
        . '<text x="110" y="130" text-anchor="middle" font-family="Inter, sans-serif" font-size="12" fill="#64748b">Total Servis</text></svg>';
?>
<?= page_head('Dasbor', 'Selamat datang di halaman dasbor <b>' . e($me['username']) . '</b> (' . ROLE_LABEL[$me['role']] . ')') ?>
<?= render_flash() ?>

<div class="row gap-20 card-grid">
  <?php foreach ($stats as [$label, $value, $icon, $bg]): ?>
  <div class="stat-card <?= $bg ?>">
    <div class="row between items-center"><div class="stat-label"><?= $label ?></div><div class="stat-icon"><?= ic($icon, 'icon icon-lg') ?></div></div>
    <div class="stat-value"><?= e($value) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row gap-20 items-start stack">
  <div class="card grow" style="min-width:0">
    <div class="card-header"><h2 class="h4"><?= $isAdmin ? 'Servis Terbaru' : 'Tugas Terbaru' ?></h2>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(url('admin/index.php?page=service')) ?>">Lihat Semua</a></div>
    <?php if (!$recent): ?>
      <div class="empty"><?= ic('wrench', 'icon icon-xl') ?>Belum ada servis.</div>
    <?php else: ?>
    <div class="table">
      <div class="tr thead"><div class="th" style="width:110px">ID</div><div class="th" style="width:170px">Pelanggan</div><div class="th" style="flex:1">Jenis Kerusakan</div><div class="th" style="width:140px">Status</div></div>
      <?php foreach ($recent as $s): ?>
      <a class="tr" href="<?= e(url('admin/index.php?page=service&view=' . (int)$s['id'])) ?>">
        <div class="td semibold" style="width:110px">#SVC-<?= (int)$s['id'] ?></div>
        <div class="td" style="width:170px"><?= e($s['nama_pelanggan']) ?></div>
        <div class="td" style="flex:1"><?= e($s['jenis_kerusakan']) ?></div>
        <div class="td" style="width:140px"><?= badge_service($s['status']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="card" style="width:460px">
    <div class="card-header"><h2 class="h4">Statistik Servis</h2></div>
    <div class="card-body" style="flex-direction:row;align-items:center;gap:24px"><?= $donut ?><div class="col gap-12 grow"><?= $legend ?></div></div>
  </div>
</div>

<?php if ($isAdmin): ?>
<div class="card">
  <div class="card-header"><h2 class="h4">Invoice Terbaru</h2>
    <a class="btn btn-outline-primary btn-sm" href="<?= e(url('admin/index.php?page=invoice')) ?>">Lihat Semua</a></div>
  <?php if (!$invoices): ?>
    <div class="empty"><?= ic('receipt', 'icon icon-xl') ?>Belum ada invoice.</div>
  <?php else: ?>
  <div class="table-wrap"><div class="table">
    <div class="tr thead"><div class="th" style="width:170px">No. Invoice</div><div class="th" style="width:140px">ID Servis</div><div class="th" style="flex:1">Pelanggan</div>
      <div class="th" style="width:180px">Total Biaya</div><div class="th" style="width:140px">Status</div><div class="th" style="width:120px">Aksi</div></div>
    <?php foreach ($invoices as $inv): ?>
    <div class="tr">
      <div class="td semibold" style="width:170px">INV-<?= (int)$inv['id'] ?></div>
      <div class="td" style="width:140px">#SVC-<?= (int)$inv['service_id'] ?></div>
      <div class="td" style="flex:1"><?= e($inv['nama_pelanggan']) ?></div>
      <div class="td" style="width:180px"><?= rp($inv['total_biaya']) ?></div>
      <div class="td" style="width:140px"><?= badge_bayar($inv['status_pembayaran']) ?></div>
      <div class="td" style="width:120px">
        <a class="btn btn-outline-primary btn-sm btn-icon" title="Lihat detail service" href="<?= e(url('admin/index.php?page=service&view=' . (int)$inv['service_id'])) ?>"><?= ic('eye', 'icon icon-sm') ?></a>
        <a class="btn btn-outline-secondary btn-sm btn-icon" title="Cetak invoice" target="_blank" href="<?= e(url('admin/index.php?page=invoice_cetak&id=' . (int)$inv['id'])) ?>"><?= ic('printer', 'icon icon-sm') ?></a>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div>
  <?php endif; ?>
</div>
<?php endif; ?>
