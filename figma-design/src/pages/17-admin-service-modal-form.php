<?php
$title = 'Admin — Modal Tambah & Edit Service';
$desc  = 'Form service: pelanggan, kerusakan, deskripsi, tanggal, teknisi & foto. Edit menambah catatan riwayat status.';
$tambah = field('Pelanggan', select('-- Pilih Pelanggan --', true), 'Pelanggan belum terdaftar? Tambahkan dulu di menu Pelanggan.')
    . field('Jenis Kerusakan', input('Contoh: Laptop tidak nyala', true))
    . field('Deskripsi', textarea('Jelaskan kerusakan secara detail...', true))
    . '<div class="row gap-16"><div class="col grow">' . field('Tanggal Masuk', '<div class="input"><span>17/10/2026 09:00</span>' . ic('calendar', 'icon icon-sm') . '</div>') . '</div><div class="col grow">' . field('Teknisi', select('-- Pilih Teknisi --', true)) . '</div></div>'
    . field('Foto Kerusakan', file_input(), 'Format: JPG, JPEG, PNG, GIF, WEBP')
    . '<div class="alert alert-info">' . ic('clock') . '<span>Service baru otomatis berstatus <b>Menunggu</b>.</span></div>';
$edit = field('Pelanggan', input('Budi Santoso', false, 'readonly'))
    . field('Jenis Kerusakan', input('Laptop Tidak Nyala'))
    . field('Deskripsi', textarea('Laptop mati total setelah terkena air, indikator charger tidak menyala.'))
    . '<div class="row gap-16"><div class="col grow">' . field('Status', select('Diproses')) . '</div><div class="col grow">' . field('Tanggal Masuk', '<div class="input"><span>12/10/2026 09:15</span>' . ic('calendar', 'icon icon-sm') . '</div>') . '</div></div>'
    . field('Teknisi', select('teknisi_andi'))
    . field('Catatan Perubahan', textarea('Contoh: Motherboard dibersihkan, menunggu pengeringan komponen.', true), 'Disimpan ke riwayat status yang dilihat pelanggan.')
    . field('Foto Kerusakan', file_input())
    . '<div class="row gap-12 items-center"><div class="thumb" style="width:120px;height:84px">' . ic('image', 'icon icon-lg') . '</div><div class="small muted">Foto saat ini</div></div>';
?>
<div class="frame">
  <?= overlay(
      modal('Tambah Service Baru', $tambah, btn('Tutup', 'secondary') . btn('Simpan Service', 'primary')),
      modal('Edit Service #SVC-125', $edit, btn('Tutup', 'secondary') . btn('Update Service', 'primary'))
  ) ?>
</div>
