<?php
// Membuat database & mengimpor skema + data awal dari database/vastacom_db.sql.
// Pemakaian (dari folder project):
//   php database/install.php           → buat database bila belum ada / masih kosong
//   php database/install.php --force   → hapus tabel lama lalu buat ulang (data lama hilang!)
// Koneksi & nama database diambil dari config/database.php (+ config/config.local.php).

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Jalankan lewat terminal: php database/install.php');
}

$c     = require __DIR__ . '/../config/database.php';
$force = in_array('--force', $argv, true);
$name  = $c['name'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($c['host'], $c['user'], $c['pass'], '', (int)$c['port']);
} catch (mysqli_sql_exception $e) {
    fwrite(STDERR, "Gagal terhubung ke MySQL ({$c['host']}:{$c['port']}): {$e->getMessage()}\n"
        . "Pastikan MySQL (Laragon/XAMPP) sudah berjalan dan setting di config/config.local.php benar.\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

$dbName = $conn->real_escape_string($name);
$tables = (int)$conn->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$dbName'")->fetch_row()[0];
if ($tables > 0 && !$force) {
    fwrite(STDERR, "Database `$name` sudah berisi $tables tabel, import dibatalkan agar data tidak terhapus.\n"
        . "Untuk menghapus dan membuat ulang dengan data awal: php database/install.php --force\n");
    exit(1);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conn->select_db($name);

// Baris CREATE DATABASE / USE dibuang agar nama database mengikuti config
$sql = file_get_contents(__DIR__ . '/vastacom_db.sql');
$sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b.*$/mi', '', $sql);

try {
    $conn->multi_query($sql);
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} catch (mysqli_sql_exception $e) {
    fwrite(STDERR, "Import gagal: {$e->getMessage()}\n");
    exit(1);
}

echo "Database `$name` siap.\n";
foreach (['users', 'pelanggan', 'product_category', 'produk', 'service', 'history_status', 'invoice'] as $t) {
    printf("  %-17s %d baris\n", $t, $conn->query("SELECT COUNT(*) FROM `$t`")->fetch_row()[0]);
}
echo "\nLogin demo: admin / teknisi1 / budi (password: vastacom123)\n";
