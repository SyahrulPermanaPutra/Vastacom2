<?php
$title = 'Admin — Modal Tambah & Edit Invoice';
$desc  = 'Form tambah invoice (pilih service) dan edit invoice.';
$tambah = field('Service', select('Pilih layanan', true))
    . field('Total Biaya', input('Total biaya', true))
    . field('Metode Pembayaran', input('Contoh: Tunai / Transfer', true))
    . field('Keterangan', textarea('Keterangan', true))
    . field('Status Pembayaran', select('pending'));
$edit = field('Total Biaya', input('750000'))
    . field('Metode Pembayaran', input('Transfer BCA'))
    . field('Keterangan', textarea('Pembersihan board + ganti IC power'))
    . field('Status Pembayaran', select('lunas'));
?>
<div class="frame">
  <?= overlay(
      modal('Tambah Invoice', $tambah, btn('Batal', 'secondary') . btn('Simpan', 'success')),
      modal('Edit Invoice', $edit, btn('Batal', 'secondary') . btn('Simpan', 'primary'), '', 'modal-header-warning')
  ) ?>
</div>
