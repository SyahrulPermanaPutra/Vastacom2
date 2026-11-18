<?php
// Aktifkan session (jika belum)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database. Nilai bawaan ada di config/database.php,
// setting per laptop di config/config.local.php (lihat config.local.example.php).
$db_config = require __DIR__ . '/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = mysqli_connect($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name'], (int)$db_config['port']);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    $hint = $e->getCode() === 1049
        ? 'Database <b>' . htmlspecialchars($db_config['name']) . '</b> belum ada. Jalankan <code>php database/install.php</code> atau import <code>database/vastacom_db.sql</code>.'
        : 'Pastikan MySQL sudah berjalan dan setting di <code>config/config.local.php</code> sudah benar.';
    die('<h3>Koneksi database gagal</h3><p>' . $hint . '</p><p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>');
}

// Path folder project di filesystem (misal: C:/laragon/www/vastacom)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)));
}

// Deteksi otomatis path URL project (misal: "/vastacom" atau "/fp-pemrograman-web"),
// sehingga nama folder hasil clone bebas. Bisa di-override lewat 'base_path' di config.local.php.
if (isset($db_config['base_path'])) {
    $base_path = $db_config['base_path'];
}
if (!isset($base_path)) {
    $base_path = '';
    $script_file = isset($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']) : '';
    $script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $root_real   = str_replace('\\', '/', realpath(ROOT_PATH) ?: ROOT_PATH);

    if ($script_file !== '' && stripos($script_file, $root_real . '/') === 0) {
        // Bagian path script relatif terhadap root project, misal "/admin/index.php"
        $relative = substr($script_file, strlen($root_real));
        if (strcasecmp(substr($script_name, -strlen($relative)), $relative) === 0) {
            $base_path = substr($script_name, 0, -strlen($relative));
        }
    } elseif (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $doc_root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']), '/');
        if (stripos($root_real, $doc_root) === 0) {
            $base_path = substr($root_real, strlen($doc_root));
        }
    }
}
$base_path = rtrim($base_path, '/');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// BASE_PATH = path URL tanpa domain (misal "/vastacom"), BASE_URL = URL lengkap.
// Keduanya TANPA garis miring di akhir, jadi pakai: BASE_URL . '/assets/...'
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $base_path);
}
if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . "://" . $http_host . $base_path);
}

mysqli_set_charset($conn, 'utf8mb4');

// Helper & autentikasi dipakai di semua halaman.
// Pesan flash ditampilkan oleh layout (render_flash), bukan lewat alert JavaScript.
require_once ROOT_PATH . '/functions/helpers.php';
require_once ROOT_PATH . '/functions/auth.php';
