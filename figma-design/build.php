<?php
// Build halaman desain statis untuk code.to.design (Figma).
// Jalankan:  php figma-design/build.php
// Hasil: figma-design/html/*.html — tiap file mandiri (CSS inline, tanpa JS),
// tinggal buka, copy seluruh isi file, lalu paste ke plugin code.to.design.

require __DIR__ . '/src/lib.php';

$css   = file_get_contents(__DIR__ . '/../assets/css/design.css');
$out   = __DIR__ . '/html';
$pages = glob(__DIR__ . '/src/pages/*.php');
sort($pages);

if (!is_dir($out)) mkdir($out, 0755, true);
array_map('unlink', glob($out . '/*.html'));

$list = [];
foreach ($pages as $file) {
    $title = '';
    $desc  = '';
    ob_start();
    include $file;            // halaman mengisi $title, $desc dan meng-echo isi frame
    $body = ob_get_clean();

    $name = basename($file, '.php') . '.html';
    $html = "<!DOCTYPE html>\n<html lang=\"id\">\n<head>\n<meta charset=\"UTF-8\">\n"
        . "<meta name=\"viewport\" content=\"width=1440\">\n"
        . "<title>Vasta Computer — {$title}</title>\n"
        . "<link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap\" rel=\"stylesheet\">\n"
        . "<style>\n{$css}\n</style>\n</head>\n<body>\n{$body}\n</body>\n</html>\n";
    file_put_contents("$out/$name", $html);
    $list[] = [$name, $title, $desc];
    echo "  ✓ $name\n";
}

// Galeri untuk preview di browser (bukan untuk di-paste ke Figma)
$items = '';
foreach ($list as [$name, $title, $desc]) {
    $items .= "<a class=\"gallery-item\" href=\"$name\"><div class=\"h4\">$title</div><div class=\"small muted\">$name</div><div class=\"body\">$desc</div></a>";
}
file_put_contents("$out/index.html", "<!DOCTYPE html><html lang=\"id\"><head><meta charset=\"UTF-8\"><title>Vasta Computer — Figma Pages</title>"
    . "<link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap\" rel=\"stylesheet\"><style>$css</style></head><body>"
    . "<div class=\"gallery\"><div class=\"col gap-8\"><div class=\"h1\">Vasta Computer — Halaman untuk Figma</div>"
    . "<div class=\"body\">Buka tiap halaman, salin seluruh source (Ctrl+U lalu Ctrl+A, Ctrl+C) atau buka filenya di editor, lalu paste ke plugin code.to.design.</div></div>"
    . "<div class=\"gallery-grid\">$items</div></div></body></html>");

echo count($list) . " halaman dibuat di figma-design/html/\n";
