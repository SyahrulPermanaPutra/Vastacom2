<?php
$title = 'Design System';
$desc  = 'Warna, tipografi, tombol, input, badge, alert, pagination & tabel.';
$sw = function ($name, $hex) {
    return '<div class="col gap-8" style="width:150px"><div style="height:80px;border-radius:12px;background:' . $hex . ';border:1px solid #e2e8f0"></div>'
        . '<div class="semibold small">' . $name . '</div><div class="small muted">' . $hex . '</div></div>';
};
$aksi = '<div class="row gap-8">' . btn('Edit', 'warning', null, 'btn-sm') . btn('Hapus', 'danger', null, 'btn-sm') . '</div>';
?>
<div class="frame frame-white">
  <div class="col gap-48" style="padding:64px 120px">
    <div class="col gap-8"><div class="h1">Vasta Computer — Design System</div><div class="lead">Komponen dasar yang dipakai di seluruh halaman publik, pelanggan, dan admin.</div></div>

    <div class="col gap-16"><div class="h3">Warna</div>
      <div class="row wrap gap-16">
        <?= $sw('Primary', '#0d6efd') . $sw('Primary Soft', '#e7f0ff') . $sw('Navbar', '#e3f2fd') . $sw('Success', '#198754') . $sw('Warning', '#ffc107') . $sw('Danger', '#dc3545') . $sw('Info', '#0dcaf0') ?>
        <?= $sw('Secondary', '#6c757d') . $sw('Dark', '#212529') . $sw('Text', '#1e293b') . $sw('Muted', '#64748b') . $sw('Border', '#e2e8f0') . $sw('Background', '#f8f9fa') ?>
      </div>
    </div>

    <div class="col gap-16"><div class="h3">Tipografi — Inter</div>
      <div class="col gap-12">
        <div class="display">Display 48 / ExtraBold</div>
        <div class="h1">Heading 1 — 32 / Bold</div>
        <div class="h2">Heading 2 — 26 / Bold</div>
        <div class="h3">Heading 3 — 20 / Bold</div>
        <div class="h4">Heading 4 — 17 / SemiBold</div>
        <div class="lead">Lead 18 — Vasta Komputer penyedia layanan servis komputer.</div>
        <div class="body">Body 15 — Teks paragraf standar untuk deskripsi dan isi tabel.</div>
        <div class="small muted">Small 13 — Keterangan tambahan</div>
      </div>
    </div>

    <div class="col gap-16"><div class="h3">Tombol</div>
      <div class="row wrap gap-12 items-center">
        <?= btn('Primary') . btn('Success', 'success') . btn('Warning', 'warning') . btn('Danger', 'danger') . btn('Info', 'info') . btn('Secondary', 'secondary') . btn('Light', 'light') . btn('Outline Primary', 'outline-primary') . btn('Outline Secondary', 'outline-secondary') . btn('Outline Dark', 'outline-dark') ?>
      </div>
      <div class="row wrap gap-12 items-center">
        <?= btn('Tambah Produk', 'primary', 'plus') . btn('Large', 'primary', null, 'btn-lg') . btn('Edit', 'warning', 'edit', 'btn-sm') . btn('Hapus', 'danger', 'trash', 'btn-sm') . btn('', 'info', 'eye', 'btn-sm btn-icon') . btn('', 'outline-secondary', 'printer', 'btn-sm btn-icon') ?>
      </div>
    </div>

    <div class="col gap-16"><div class="h3">Form</div>
      <div class="row gap-24">
        <div class="col gap-16" style="width:360px">
          <?= field('Input default', input('Lenovo Ideapad Gaming')) ?>
          <?= field('Placeholder', input('Nama Produk', true)) ?>
          <?= field('Focus', input('budi@email.com', false, 'focus')) ?>
          <?= field('Readonly', input('Budi Santoso', false, 'readonly')) ?>
        </div>
        <div class="col gap-16" style="width:360px">
          <?= field('Select', select('-- Semua Kategori --')) ?>
          <?= field('Textarea', textarea('Tulis deskripsi kerusakan...', true)) ?>
          <?= field('Upload file', file_input()) ?>
        </div>
        <div class="col gap-16" style="width:360px">
          <?= field('Radio', radios(['Admin', 'Teknisi', 'Pelanggan'], 0)) ?>
          <?= field('Input group', '<div class="input-group">' . input('Cari pelanggan...', true) . btn('Cari', 'outline-secondary', 'search') . '</div>') ?>
        </div>
      </div>
    </div>

    <div class="col gap-16"><div class="h3">Badge Status</div>
      <div class="row wrap gap-12">
        <?= badge('menunggu', 'Menunggu') . badge('diproses', 'Diproses') . badge('selesai', 'Selesai') . badge('batal', 'Dibatalkan') . badge('aktif', 'Aktif') . badge('nonaktif', 'Nonaktif') . badge('selesai', 'Lunas') . badge('menunggu', 'Pending') . badge('batal', 'Batal') . badge('admin', 'Admin') . badge('teknisi', 'Teknisi') . badge('pelanggan', 'Pelanggan') ?>
      </div>
    </div>

    <div class="col gap-16"><div class="h3">Alert &amp; Pagination</div>
      <div class="col gap-12" style="width:720px">
        <div class="alert alert-success"><?= ic('check') ?><span class="grow">Data service berhasil <b>ditambahkan</b>.</span><?= ic('x', 'icon icon-sm') ?></div>
        <div class="alert alert-danger"><?= ic('alert') ?><span class="grow">Terjadi kesalahan saat memproses data service.</span><?= ic('x', 'icon icon-sm') ?></div>
        <div class="alert alert-info"><?= ic('alert') ?><span class="grow">Belum ada riwayat status.</span></div>
      </div>
      <div class="row"><?= pagination(3, 1) ?></div>
    </div>

    <div class="col gap-16"><div class="h3">Tabel</div>
      <div class="card" style="width:900px">
        <?= table([['No', 70], ['Nama', null], ['Harga', 160], ['Status', 140], ['Aksi', 170]], [
            ['1', 'Lenovo Ideapad Gaming', rp(13899000), badge('aktif', 'Aktif'), $aksi],
            ['2', 'Dell Latitude R', rp(3450000), badge('nonaktif', 'Nonaktif'), $aksi],
        ]) ?>
      </div>
    </div>
  </div>
</div>
