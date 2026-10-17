<?php
$title = 'Admin — Data Pelanggan';
$desc  = 'Tabel pelanggan dengan pencarian, jumlah data per halaman & pagination.';
$pel = [
    ['Budi Santoso', 'budi@email.com', 'budi', '0812-3456-7890', '02 Okt 2026'],
    ['Ani Wijaya', 'ani.wijaya@email.com', 'ani', '0813-2222-1111', '03 Okt 2026'],
    ['Rudi Hartono', 'rudi.h@email.com', 'rudi', '0857-1234-5678', '05 Okt 2026'],
    ['Siti Rahayu', 'siti.rahayu@email.com', 'siti', '0821-9876-5432', '06 Okt 2026'],
    ['Dewi Lestari', 'dewi@email.com', 'dewi', '0815-5555-4444', '09 Okt 2026'],
    ['Agus Pratama', 'agus.p@email.com', 'agus', '-', '11 Okt 2026'],
];
$rows = [];
foreach ($pel as $i => $p) {
    $rows[] = [$i + 1, '<span class="semibold">' . $p[0] . '</span>', $p[1], $p[2], $p[3], '<span class="muted">' . $p[4] . '</span>', aksi_edit_hapus()];
}
$content = page_head('Data Pelanggan', 'Daftar pelanggan yang terdaftar di Vasta Computer.', btn('Tambah Pelanggan', 'primary', 'plus'))
    . '<div class="card">'
    . '<div class="card-body" style="flex-direction:row;justify-content:space-between;align-items:center">'
    .   '<div class="input-group" style="width:420px">' . input('Cari pelanggan...', true) . btn('Cari', 'outline-secondary', 'search') . '</div>'
    .   select('10 Data')
    . '</div>'
    . table([['No', 60], ['Nama', null], ['Email', 240], ['Username', 130], ['No. HP', 170], ['Dibuat Pada', 150], ['Aksi', 200]], $rows)
    . '<div class="card-body" style="flex-direction:row;justify-content:space-between;align-items:center;border-top:1px solid #e2e8f0"><div class="small muted">Menampilkan 1–6 dari 24 pelanggan</div>' . pagination(3, 1) . '</div>'
    . '</div>';
echo admin_frame('pelanggan', $content);
