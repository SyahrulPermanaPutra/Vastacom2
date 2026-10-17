<?php
$title = 'Home (Landing Page)';
$desc  = 'Hero, layanan, produk, keunggulan, statistik, kontak & lokasi.';
$feature = function ($icon, $t, $d) {
    return '<div class="card card-shadow feature-card"><div class="feature-icon">' . ic($icon, 'icon icon-lg') . '</div><div class="h3">' . $t . '</div><div class="body">' . $d . '</div></div>';
};
?>
<div class="frame frame-white">
  <?= nav_public('home') ?>

  <div class="section hero">
    <div class="container"><div class="row items-center between">
      <div class="col gap-24" style="width:560px">
        <div class="badge badge-admin" style="align-self:flex-start"><?= ic('wrench', 'icon icon-sm') ?><span>Servis Komputer &amp; Laptop Terpercaya</span></div>
        <div class="display">Professional Komputer &amp; Laptop Repair</div>
        <div class="lead">Vasta Komputer adalah penyedia layanan servis komputer yang berkomitmen untuk memberikan solusi teknologi terbaik bagi individu dan bisnis. Dengan pengalaman bertahun-tahun, kami siap membantu kebutuhan perangkat Anda.</div>
        <div class="row gap-12"><?= btn('Our Services', 'primary', null, 'btn-lg') . btn('Contact Us', 'outline-dark', null, 'btn-lg') ?></div>
      </div>
      <img class="hero-img" src="https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?auto=format&amp;fit=crop&amp;w=1160&amp;q=80" alt="Laptop Repair">
    </div></div>
  </div>

  <div class="section section-white">
    <div class="container">
      <div class="section-head"><div class="h2">Layanan Kami</div><div class="title-bar"></div><div class="body">Kami menawarkan berbagai layanan untuk memastikan komputer / laptop Anda berfungsi optimal</div></div>
      <div class="row gap-24">
        <?= $feature('cpu', 'Perbaikan Hardware', 'Mengganti atau memperbaiki komponen seperti motherboard, RAM, hard disk, dan lainnya.') ?>
        <?= $feature('bug', 'Perbaikan Software &amp; Pemeliharaan', 'Instalasi ulang sistem operasi, penghapusan virus, pembersihan sistem dan pembaruan perangkat lunak secara berkala.') ?>
        <?= $feature('database', 'Pemulihan Data &amp; Peningkatan Performa', 'Pemulihan data rusak pada penyimpanan. Upgrade perangkat keras dan lunak untuk meningkatkan kecepatan dan efisiensi.') ?>
      </div>
    </div>
  </div>

  <div class="section section-light">
    <div class="container">
      <div class="section-head"><div class="h2">Produk Kami</div><div class="title-bar"></div></div>
      <div class="row wrap gap-24">
        <?php foreach ($PRODUK as $p): if (!$p[3]) continue; ?>
        <div class="card card-shadow product-card"><?= product_img() ?><div class="product-body"><div class="small muted"><?= $p[2] ?></div><div class="h4"><?= $p[0] ?></div><div class="price"><?= rp($p[1]) ?></div></div></div>
        <?php endforeach; ?>
        <div class="card card-shadow product-card"><?= product_img() ?><div class="product-body"><div class="small muted">Aksesoris</div><div class="h4">Mouse Logitech M331</div><div class="price"><?= rp(219000) ?></div></div></div>
      </div>
    </div>
  </div>

  <div class="section section-light" style="padding-top:0">
    <div class="container">
      <div class="section-head"><div class="h2">Kenapa harus Vasta Komputer?</div><div class="title-bar"></div></div>
      <div class="row gap-24">
        <?= $feature('clock', 'Fast Service', 'Sebagian besar perbaikan selesai dalam waktu 24-48 jam dengan pembaruan status waktu nyata.') ?>
        <?= $feature('shield', 'Garansi', 'Garansi 90 hari untuk semua perbaikan dan suku cadang demi ketenangan pikiran Anda.') ?>
        <?= $feature('user', 'Experts', 'Teknisi berpengalaman 10+ tahun dalam perbaikan laptop.') ?>
        <?= $feature('bell', 'Notifications', 'Dapatkan notifikasi setiap perubahan status melalui dashboard pelanggan yang telah terdaftar.') ?>
      </div>
    </div>
  </div>

  <div class="stats">
    <div class="container"><div class="row between text-center">
      <?php foreach ([['120+', 'Laptops Diperbaiki'], ['85+', 'Customer Kami'], ['24+', 'Products Tersedia'], ['24/7', 'Customer Support']] as $s): ?>
      <div class="col gap-8 items-center" style="width:280px"><div class="stat-big"><?= $s[0] ?></div><div class="stat-sub"><?= $s[1] ?></div></div>
      <?php endforeach; ?>
    </div></div>
  </div>

  <div class="footer">
    <div class="container gap-32">
      <div class="row between">
        <div class="col gap-20" style="width:540px">
          <div class="brand" style="color:#fff"><div class="brand-logo"><?= ic('laptop') ?></div><span>Vasta Computer</span></div>
          <div class="h3" style="color:#fff">Kontak Kami</div>
          <div class="row items-center gap-12"><div class="brand-logo" style="background:#25d366"><?= ic('phone', 'icon icon-sm') ?></div><div class="h4" style="color:#fff">0815-5409-1512</div></div>
          <div class="row items-start gap-12"><div class="brand-logo"><?= ic('pin', 'icon icon-sm') ?></div><div class="body" style="color:#dee2e6;width:460px">Jl. Granit Nila No.20A, Paras, Mulung, Kec. Driyorejo, Kabupaten Gresik, Jawa Timur 61177</div></div>
        </div>
        <div class="col gap-16">
          <div class="h4" style="color:#fff">Lokasi Kami</div>
          <div class="map"><?= ic('pin', 'icon icon-xl') ?><div class="semibold">Google Maps — Vasta Computer</div><div class="small">Driyorejo, Gresik</div></div>
        </div>
      </div>
      <div class="copyright">© 2026 Vasta Computer. Service — Jual Beli Laptop.</div>
    </div>
  </div>
</div>
