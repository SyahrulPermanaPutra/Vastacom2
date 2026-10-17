<?php
// Helper umum aplikasi: escape, redirect, flash message, format, upload, pagination.
require_once __DIR__ . '/ui.php';

const STATUS_SERVICE = [
    'menunggu'   => ['Menunggu', 'menunggu'],
    'diproses'   => ['Diproses', 'diproses'],
    'selesai'    => ['Selesai', 'selesai'],
    'dibatalkan' => ['Dibatalkan', 'batal'],
];
const STATUS_BAYAR = [
    'pending' => ['Pending', 'menunggu'],
    'lunas'   => ['Lunas', 'selesai'],
    'batal'   => ['Batal', 'batal'],
];
const ROLE_LABEL = ['admin' => 'Admin', 'teknisi' => 'Teknisi', 'pelanggan' => 'Pelanggan'];

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

function flash($type, $message) {
    $_SESSION['flash'][] = [$type, $message];
}

function render_flash() {
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as [$type, $msg]) {
        $icon = $type === 'success' ? 'check' : 'alert';
        $html .= '<div class="alert alert-' . e($type) . '" data-alert>' . ic($icon) . '<span class="grow">' . $msg . '</span>'
            . '<button type="button" class="alert-close" data-dismiss aria-label="Tutup">' . ic('x', 'icon icon-sm') . '</button></div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

function rp($n) {
    return 'Rp' . number_format((float)$n, 0, ',', '.');
}

function tgl($datetime, $withTime = true) {
    if (!$datetime) return '-';
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($datetime);
    return date('d', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t) . ($withTime ? ' ' . date('H:i', $t) : '');
}

function badge($variant, $text) {
    return '<span class="badge badge-' . e($variant) . '"><span class="badge-dot"></span>' . e($text) . '</span>';
}

function badge_service($status) {
    [$label, $variant] = STATUS_SERVICE[$status] ?? [ucfirst((string)$status), 'nonaktif'];
    return badge($variant, $label);
}

function badge_bayar($status) {
    [$label, $variant] = STATUS_BAYAR[$status] ?? [ucfirst((string)$status), 'nonaktif'];
    return badge($variant, $label);
}

function initials($text) {
    $p = preg_split('/[\s_]+/', trim((string)$text));
    $s = strtoupper(substr($p[0], 0, 1) . (isset($p[1]) && $p[1] !== '' ? substr($p[1], 0, 1) : substr($p[0], 1, 1)));
    return $s !== '' ? $s : '?';
}

function db_all($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function db_one($conn, $sql, $types = '', ...$params) {
    return db_all($conn, $sql, $types, ...$params)[0] ?? null;
}

function db_exec($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

/**
 * Simpan file upload gambar. Mengembalikan nama file baru, '' jika tidak ada file,
 * atau false jika format tidak valid / gagal.
 */
function upload_image($field, $dir, $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    if (empty($_FILES[$field]['name'])) return '';
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return false;
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true) || @getimagesize($_FILES[$field]['tmp_name']) === false) return false;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = uniqid() . '.' . $ext;
    return move_uploaded_file($_FILES[$field]['tmp_name'], rtrim($dir, '/') . '/' . $name) ? $name : false;
}

function delete_file($dir, $name) {
    $path = rtrim($dir, '/') . '/' . basename((string)$name);
    if ($name && is_file($path)) unlink($path);
}

/** URL gambar produk, fallback ke default.jpg bila file tidak ada. */
function product_image_url($gambar) {
    $gambar = basename((string)$gambar);
    return ($gambar && is_file(ROOT_PATH . '/assets/image/' . $gambar))
        ? url('assets/image/' . rawurlencode($gambar))
        : url('assets/image/default.jpg');
}

/** URL foto kerusakan service, null bila tidak ada file. */
function service_photo_url($foto) {
    $foto = basename((string)$foto);
    return ($foto && is_file(ROOT_PATH . '/uploads/' . $foto)) ? url('uploads/' . rawurlencode($foto)) : null;
}

/** Data pagination: [limit, page, offset, total_pages]. */
function paginate($total, $limitOptions = [10, 20, 50, 100]) {
    $limit = (int)($_GET['limit'] ?? 10);
    if (!in_array($limit, $limitOptions, true)) $limit = $limitOptions[0];
    $pages = max(1, (int)ceil($total / $limit));
    $page  = min($pages, max(1, (int)($_GET['p'] ?? 1)));
    return [$limit, $page, ($page - 1) * $limit, $pages];
}

/** Query string yang mempertahankan parameter GET saat ini. */
function qs($changes = []) {
    $q = array_merge($_GET, $changes);
    foreach ($q as $k => $v) if ($v === null || $v === '') unset($q[$k]);
    return '?' . http_build_query($q);
}

function pagination_links($page, $pages) {
    $h = '<nav class="pagination">';
    $h .= $page > 1 ? '<a class="page" href="' . e(qs(['p' => $page - 1])) . '">' . ic('left', 'icon icon-sm') . '</a>' : '<span class="page disabled">' . ic('left', 'icon icon-sm') . '</span>';
    for ($i = 1; $i <= $pages; $i++) {
        $h .= '<a class="page' . ($i === $page ? ' active' : '') . '" href="' . e(qs(['p' => $i])) . '">' . $i . '</a>';
    }
    $h .= $page < $pages ? '<a class="page" href="' . e(qs(['p' => $page + 1])) . '">' . ic('right', 'icon icon-sm') . '</a>' : '<span class="page disabled">' . ic('right', 'icon icon-sm') . '</span>';
    return $h . '</nav>';
}

/** Hidden input token CSRF. */
function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="_token" value="' . $_SESSION['csrf'] . '">';
}

/** Tolak POST tanpa token CSRF yang valid. */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['_token'] ?? '')) {
        flash('danger', 'Sesi formulir kedaluwarsa, silakan coba lagi.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

/** Tombol hapus: form POST + modal konfirmasi (lihat assets/js/app.js). */
function delete_button($action, $id, $message, $label = 'Hapus', $iconOnly = false) {
    return '<form method="post" class="inline-form" data-confirm="' . e($message) . '">' . csrf_field()
        . '<input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="id" value="' . (int)$id . '">'
        . '<button type="submit" class="btn btn-danger btn-sm' . ($iconOnly ? ' btn-icon' : '') . '" title="' . e($label) . '">' . ic('trash', 'icon icon-sm') . ($iconOnly ? '' : '<span>' . e($label) . '</span>') . '</button></form>';
}

function modal_open($id, $title, $size = '', $headerClass = '') {
    return '<div class="overlay" id="' . e($id) . '" data-modal><div class="modal ' . $size . '" role="dialog" aria-modal="true">'
        . '<div class="modal-header ' . $headerClass . '"><div class="modal-title">' . $title . '</div>'
        . '<button type="button" class="modal-close" data-close aria-label="Tutup">' . ic('x') . '</button></div>';
}

function modal_close() {
    return '</div></div>';
}
