<?php
// Contoh setting lokal. Salin file ini menjadi config/config.local.php
// lalu ubah hanya nilai yang berbeda di laptop Anda (file tersebut tidak ikut di-commit).

return [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',            // XAMPP/Laragon default: kosong
    'name' => 'vastacom_db',
    'port' => 3306,          // ganti jika MySQL memakai port lain, misal 3307

    // Opsional: paksa path URL project bila deteksi otomatis tidak sesuai.
    // Contoh: '/Vastacom2', atau '' bila memakai virtual host (vastacom2.test)
    // 'base_path' => '/Vastacom2',
];
