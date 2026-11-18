<?php
// Landing page (Figma: 01-home)
$produk = db_all($conn, "SELECT p.*, c.nama AS kategori FROM produk p LEFT JOIN product_category c ON p.catg_id = c.id
                         WHERE p.status = '1' ORDER BY p.id DESC LIMIT 8");
$total_service   = db_one($conn, "SELECT COUNT(*) n FROM service")['n'];
$total_pelanggan = db_one($conn, "SELECT COUNT(*) n FROM pelanggan")['n'];
$total_produk    = db_one($conn, "SELECT COUNT(*) n FROM produk WHERE status = '1'")['n'];

$feature = function ($icon, $title, $text) {
    return '<div class="card card-shadow feature-card"><div class="feature-icon">' . ic($icon, 'icon icon-lg') . '</div><h3 class="h3">' . $title . '</h3><p class="body">' . $text . '</p></div>';
};
$flash = render_flash();
?>
<?php if ($flash): ?><div class="container" style="padding-top:20px"><div class="flash-stack"><?= $flash ?></div></div><?php endif; ?>

<section id="home" class="section hero">
  <div class="container"><div class="row items-center between gap-32 stack">
    <div class="col gap-24" style="width:560px">
      <span class="badge badge-admin" style="align-self:flex-start"><?= ic('wrench', 'icon icon-sm') ?>Servis Komputer &amp; Laptop Terpercaya</span>
      <h1 class="display">Servis Komputer &amp; Laptop Profesional</h1>
      <p class="lead">Vasta Komputer adalah penyedia layanan servis komputer yang berkomitmen untuk memberikan solusi teknologi terbaik bagi individu dan bisnis. Dengan pengalaman bertahun-tahun, kami siap membantu kebutuhan perangkat Anda.</p>
      <div class="row gap-12">
        <a href="#services" class="btn btn-primary btn-lg">Layanan Kami</a>
        <a href="#contact" class="btn btn-outline-dark btn-lg">Hubungi Kami</a>
      </div>
    </div>
    <img class="hero-img" src="https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?auto=format&amp;fit=crop&amp;w=1160&amp;q=80" alt="Servis laptop">
  </div></div>
</section>

<section id="services" class="section section-white">
  <div class="container">
    <div class="section-head"><h2 class="h2">Layanan Kami</h2><div class="title-bar"></div><p class="body text-center">Kami menawarkan berbagai layanan untuk memastikan komputer / laptop Anda berfungsi optimal</p></div>
    <div class="row gap-24 stack">
      <?= $feature('cpu', 'Perbaikan Hardware', 'Mengganti atau memperbaiki komponen seperti motherboard, RAM, hard disk, dan lainnya.') ?>
      <?= $feature('bug', 'Perbaikan Software &amp; Pemeliharaan', 'Instalasi ulang sistem operasi, penghapusan virus, pembersihan sistem dan pembaruan perangkat lunak secara berkala.') ?>
      <?= $feature('database', 'Pemulihan Data &amp; Peningkatan Performa', 'Pemulihan data rusak pada penyimpanan. Upgrade perangkat keras dan lunak untuk meningkatkan kecepatan dan efisiensi.') ?>
    </div>
  </div>
</section>

<section id="products" class="section section-light">
  <div class="container">
    <div class="section-head"><h2 class="h2">Produk Kami</h2><div class="title-bar"></div></div>
    <?php if (!$produk): ?>
      <div class="empty"><?= ic('package', 'icon icon-xl') ?>Belum ada produk yang ditampilkan.</div>
    <?php else: ?>
    <div class="row wrap gap-24">
      <?php foreach ($produk as $p): ?>
      <div class="card card-shadow product-card">
        <img class="product-photo" src="<?= e(product_image_url($p['gambar'])) ?>" alt="<?= e($p['nama']) ?>" loading="lazy">
        <div class="product-body">
          <div class="small muted"><?= e($p['kategori'] ?? 'Produk') ?></div>
          <h3 class="h4"><?= e($p['nama']) ?></h3>
          <div class="price"><?= rp($p['harga']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section id="why-us" class="section section-light" style="padding-top:0">
  <div class="container">
    <div class="section-head"><h2 class="h2">Kenapa harus Vasta Komputer?</h2><div class="title-bar"></div></div>
    <div class="row gap-24 stack">
      <?= $feature('clock', 'Servis Cepat', 'Sebagian besar perbaikan selesai dalam waktu 24-48 jam dengan pembaruan status waktu nyata.') ?>
      <?= $feature('shield', 'Garansi', 'Garansi 90 hari untuk semua perbaikan dan suku cadang demi ketenangan pikiran Anda.') ?>
      <?= $feature('user', 'Teknisi Ahli', 'Teknisi berpengalaman 10+ tahun dalam perbaikan laptop.') ?>
      <?= $feature('bell', 'Pantau Status', 'Pantau setiap perubahan status servis melalui dasbor pelanggan.') ?>
    </div>
  </div>
</section>

<section class="stats">
  <div class="container"><div class="row between text-center stack gap-24">
    <?php foreach ([[$total_service . '+', 'Laptop Diperbaiki'], [$total_pelanggan . '+', 'Pelanggan Kami'], [$total_produk . '+', 'Produk Tersedia'], ['24/7', 'Layanan Pelanggan']] as $s): ?>
    <div class="col gap-8 items-center" style="width:280px"><div class="stat-big"><?= e($s[0]) ?></div><div class="stat-sub"><?= $s[1] ?></div></div>
    <?php endforeach; ?>
  </div></div>
</section>

<footer id="contact" class="footer">
  <div class="container gap-32">
    <div class="row between gap-32 stack">
      <div class="col gap-20" style="width:540px">
        <div class="brand" style="color:#fff"><span class="brand-logo"><?= ic('laptop') ?></span><span>Vasta Computer</span></div>
        <h3 class="h3" style="color:#fff">Kontak Kami</h3>
        <a class="row items-center gap-12" href="https://wa.me/6281554091512" target="_blank" rel="noopener"><span class="brand-logo" style="background:#25d366"><?= ic('phone', 'icon icon-sm') ?></span><span class="h4" style="color:#fff">0815-5409-1512</span></a>
        <div class="row items-start gap-12"><span class="brand-logo"><?= ic('pin', 'icon icon-sm') ?></span><p class="body" style="color:#dee2e6;max-width:460px">Jl. Granit Nila No.20A, Paras, Mulung, Kec. Driyorejo, Kabupaten Gresik, Jawa Timur 61177</p></div>
      </div>
      <div class="col gap-16">
        <h4 class="h4" style="color:#fff">Lokasi Kami</h4>
        <iframe class="map-frame" src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7563.950085951548!2d112.634711!3d-7.3357081!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fd9d579a9af3%3A0xe4b78879228481a5!2sVasta%20Computer%20Driyorejo!5e1!3m2!1sid!2sid!4v1750265551918!5m2!1sid!2sid" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi Vasta Computer"></iframe>
      </div>
    </div>
    <div class="copyright">© <?= date('Y') ?> Vasta Computer. Servis & Jual Beli Laptop.</div>
  </div>
</footer>
