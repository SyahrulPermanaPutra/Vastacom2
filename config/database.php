<?php
// Pengaturan koneksi database (dipakai config.php dan database/install.php).
// Jangan ubah nilai di sini untuk kebutuhan laptop masing-masing:
// salin config/config.local.example.php menjadi config/config.local.php lalu sesuaikan.

$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'vastacom_db',
    'port' => 3306,
];

if (is_file(__DIR__ . '/config.local.php')) {
    $local = require __DIR__ . '/config.local.php';
    if (is_array($local)) {
        $db_config = array_merge($db_config, $local);
    }
}

return $db_config;
