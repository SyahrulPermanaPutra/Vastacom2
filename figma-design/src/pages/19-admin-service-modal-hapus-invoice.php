<?php
$title = 'Admin — Modal Konfirmasi Hapus & Buat Invoice';
$desc  = 'Konfirmasi hapus service dan form buat invoice dari halaman service.';
$hapus = '<div class="row gap-16 items-start"><div class="stat-icon" style="background:#f8d7da;color:#dc3545">' . ic('trash', 'icon icon-lg') . '</div>'
    . '<div class="col gap-6 grow" style="gap:6px"><div class="h4">Hapus service ini?</div><div class="body">Service <b>#SVC-125 — Laptop Tidak Nyala</b> milik Budi Santoso akan dihapus permanen beserta foto kerusakannya.</div></div></div>';
$invoice = '<div class="alert alert-info">' . ic('wrench') . '<span>Service <b>#SVC-125</b> — Budi Santoso · Laptop Tidak Nyala</span></div>'
    . field('Total Biaya', '<div class="input"><span class="muted">Rp</span><span class="grow">750000</span></div>')
    . field('Metode Pembayaran', input('Contoh: Tunai / Transfer BCA', true))
    . field('Status Pembayaran', select('Pending'))
    . field('Keterangan', textarea('Catatan tambahan (opsional)', true));
?>
<div class="frame">
  <?= overlay(
      modal('Konfirmasi Hapus', $hapus, btn('Batal', 'secondary') . btn('Hapus', 'danger', 'trash'), 'modal-sm'),
      modal('Buat Invoice', $invoice, btn('Batal', 'secondary') . btn('Simpan Invoice', 'primary'))
  ) ?>
</div>
