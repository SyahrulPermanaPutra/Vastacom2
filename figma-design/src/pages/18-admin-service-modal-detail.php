<?php
// Panel admin memakai komponen detail yang sama dengan dashboard pelanggan (05),
// ditambah tombol cetak invoice.
ob_start();
include __DIR__ . '/05-dashboard-pelanggan-detail.php';
ob_end_clean();
$title = 'Admin — Modal Detail Service';
$desc  = 'Komponen detail yang sama dengan dashboard pelanggan + tombol cetak invoice.';
$body  = str_replace('<div class="h4">Informasi Invoice</div>',
    '<div class="row between items-center"><div class="h4">Informasi Invoice</div>' . btn('Cetak', 'outline-secondary', 'printer', 'btn-sm') . '</div>', $body);
?>
<div class="frame">
  <?= overlay(modal('Detail Service', $body, btn('Tutup', 'secondary'), 'modal-lg')) ?>
</div>
