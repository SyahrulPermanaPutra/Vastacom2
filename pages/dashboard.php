<?php
// Dashboard pelanggan (Figma: 04 & 05)
require_once ROOT_PATH . '/functions/service.php';

$pelanggan = db_one($conn, "SELECT * FROM pelanggan WHERE user_id = ?", 'i', $me['id']);
$services  = $pelanggan
    ? db_all($conn, SERVICE_SELECT . " WHERE s.id_pelanggan = ? ORDER BY s.tanggal_masuk DESC", 'i', $pelanggan['id'])
    : [];
$count = array_count_values(array_column($services, 'status'));
$cards = [
    ['Total Service', count($services), 'wrench', '#0d6efd'],
    ['Sedang Diproses', $count['diproses'] ?? 0, 'clock', '#0aa2c0'],
    ['Selesai', $count['selesai'] ?? 0, 'check', '#198754'],
];
?>
<div class="container gap-24" style="padding-top:40px;padding-bottom:56px">
  <?= render_flash() ?>
  <div class="col gap-8">
    <h1 class="h1">Selamat Datang, <?= e($pelanggan['nama'] ?? $me['username']) ?></h1>
    <p class="body">Berikut daftar perangkat yang sedang dan pernah Anda servis di Vasta Computer.</p>
  </div>

  <div class="row gap-16 stack">
    <?php foreach ($cards as [$label, $value, $icon, $color]): ?>
    <div class="card grow"><div class="card-body" style="flex-direction:row;align-items:center">
      <div class="stat-icon" style="background:<?= $color ?>1a;color:<?= $color ?>"><?= ic($icon) ?></div>
      <div class="col gap-4"><div class="small muted semibold"><?= $label ?></div><div class="h2"><?= (int)$value ?></div></div>
    </div></div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="card-header"><h2 class="h4">Riwayat Service</h2></div>
    <?php if (!$services): ?>
      <div class="empty"><?= ic('laptop', 'icon icon-xl') ?>Belum ada data service. Bawa perangkat Anda ke toko kami untuk mulai servis.</div>
    <?php else: ?>
    <div class="table-wrap"><div class="table">
      <div class="tr thead">
        <div class="th" style="width:60px">#</div><div class="th" style="flex:1">Jenis Kerusakan</div><div class="th" style="width:160px">Status</div>
        <div class="th" style="width:180px">Teknisi</div><div class="th" style="width:200px">Tanggal Masuk</div><div class="th" style="width:130px">Aksi</div>
      </div>
      <?php foreach ($services as $i => $s): ?>
      <div class="tr">
        <div class="td" style="width:60px"><?= $i + 1 ?></div>
        <div class="td semibold" style="flex:1"><?= e($s['jenis_kerusakan']) ?></div>
        <div class="td" style="width:160px"><?= badge_service($s['status']) ?></div>
        <div class="td" style="width:180px"><?= e($s['nama_teknisi'] ?: '-') ?></div>
        <div class="td" style="width:200px"><?= tgl($s['tanggal_masuk']) ?></div>
        <div class="td" style="width:130px"><button type="button" class="btn btn-outline-primary btn-sm" data-open="detail-<?= (int)$s['id'] ?>"><?= ic('eye', 'icon icon-sm') ?><span>Detail</span></button></div>
      </div>
      <?php endforeach; ?>
    </div></div>
    <?php endif; ?>
  </div>
</div>

<?php foreach ($services as $s): ?>
  <?= modal_open('detail-' . (int)$s['id'], 'Detail Service', 'modal-lg') ?>
  <div class="modal-body"><?= service_detail_body($conn, $s) ?></div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-close>Tutup</button></div>
  <?= modal_close() ?>
<?php endforeach; ?>
