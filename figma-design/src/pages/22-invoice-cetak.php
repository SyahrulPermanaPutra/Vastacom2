<?php
$title = 'Cetak Invoice (PDF A4)';
$desc  = 'Tampilan dokumen invoice yang dicetak ke PDF (Dompdf).';
$line = '<div style="height:1px;background:#e2e8f0"></div>';
?>
<div class="paper-wrap">
  <div class="paper">
    <div class="row between items-start">
      <div class="col gap-8">
        <div class="brand"><div class="brand-logo"><?= ic('laptop') ?></div><span>Vasta Computer</span></div>
        <div class="small muted" style="width:300px">Jl. Granit Nila No.20A, Driyorejo, Gresik, Jawa Timur 61177 · 0815-5409-1512</div>
      </div>
      <div class="col gap-4" style="align-items:flex-end">
        <div style="font-size:34px;font-weight:800;letter-spacing:2px;color:#0d6efd">INVOICE</div>
        <div class="semibold">INV-31</div>
        <div class="small muted">Tanggal: 14-10-2026</div>
      </div>
    </div>
    <?= $line ?>
    <div class="row between">
      <div class="col gap-4"><div class="small muted semibold">DITAGIHKAN KEPADA</div><div class="h4">Budi Santoso</div><div class="small muted">budi@email.com · 0812-3456-7890</div></div>
      <div class="col gap-4" style="align-items:flex-end"><div class="small muted semibold">STATUS PEMBAYARAN</div><?= badge('menunggu', 'Pending') ?></div>
    </div>
    <div class="col gap-4"><div class="small muted semibold">JENIS KERUSAKAN</div><div class="body semibold" style="color:#212529">Laptop Tidak Nyala</div>
      <div class="body">Laptop mati total setelah terkena air, indikator charger tidak menyala.</div></div>
    <div class="table table-bordered">
      <div class="tr thead"><div class="th" style="flex:1">Item</div><div class="th" style="width:200px;justify-content:flex-end">Biaya</div></div>
      <div class="tr"><div class="td" style="flex:1">Perbaikan — Laptop Tidak Nyala</div><div class="td" style="width:200px;justify-content:flex-end">Rp750.000</div></div>
      <div class="tr" style="background:#f8fafc"><div class="td bold" style="flex:1">Total</div><div class="td bold" style="width:200px;justify-content:flex-end;font-size:18px;color:#0d6efd">Rp750.000</div></div>
    </div>
    <div class="row gap-48">
      <div class="col gap-4"><div class="small muted semibold">METODE PEMBAYARAN</div><div class="body">Transfer BCA</div></div>
      <div class="col gap-4"><div class="small muted semibold">DIBUAT OLEH</div><div class="body">admin</div></div>
    </div>
    <div class="grow"></div>
    <?= $line ?>
    <div class="small muted text-center">Terima kasih telah mempercayakan perangkat Anda kepada Vasta Computer. Garansi servis 90 hari.</div>
  </div>
</div>
