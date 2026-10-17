<?php
$title = 'Dashboard Pelanggan';
$desc  = 'Daftar service milik pelanggan beserta status & teknisi.';
$rows = [];
foreach (array_slice($SERVICES, 0, 4) as $i => $s) {
    $rows[] = [$i + 1, '<span class="semibold">' . $s[1] . '</span>', badge($s[3], $STATUS_LABEL[$s[3]]), $s[5] === 'N/A' ? '-' : $s[5], $s[4], btn('Detail', 'outline-primary', 'eye', 'btn-sm')];
}
$cards = [['Total Service', '4', 'wrench', '#0d6efd'], ['Sedang Diproses', '1', 'clock', '#0aa2c0'], ['Selesai', '1', 'check', '#198754']];
?>
<div class="frame" style="min-height:900px">
  <?= nav_public('dashboard', 'dashboard') ?>
  <div class="container gap-24" style="padding:40px 0">
    <div class="col gap-8"><div class="h1">Selamat Datang, Budi Santoso</div><div class="body">Berikut daftar perangkat yang sedang dan pernah Anda servis di Vasta Computer.</div></div>
    <div class="row gap-16">
      <?php foreach ($cards as $c): ?>
      <div class="card grow"><div class="card-body" style="flex-direction:row;align-items:center"><div class="stat-icon" style="background:<?= $c[3] ?>1a;color:<?= $c[3] ?>"><?= ic($c[2]) ?></div><div class="col gap-4"><div class="small muted semibold"><?= $c[0] ?></div><div class="h2"><?= $c[1] ?></div></div></div></div>
      <?php endforeach; ?>
    </div>
    <div class="card">
      <div class="card-header"><div class="h4">Riwayat Service</div></div>
      <?= table([['#', 60], ['Jenis Kerusakan', null], ['Status', 160], ['Teknisi', 180], ['Tanggal Masuk', 200], ['Aksi', 130]], $rows) ?>
    </div>
  </div>
</div>
