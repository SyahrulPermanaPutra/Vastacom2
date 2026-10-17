<?php
$title = 'Admin — Service Management';
$desc  = 'Notifikasi, pencarian, tabel service dengan badge status & aksi.';
$rows = [];
foreach ($SERVICES as $i => $s) {
    $desc_short = strlen($s[2]) > 50 ? substr($s[2], 0, 50) . '...' : $s[2];
    $rows[] = [$i + 1, '<span class="semibold">' . $s[0] . '</span>', $s[1], '<span class="muted">' . $desc_short . '</span>', badge($s[3], $STATUS_LABEL[$s[3]]), $s[4], $s[5],
        '<div class="row gap-6" style="gap:6px">' . btn('', 'warning', 'edit', 'btn-sm btn-icon') . btn('', 'danger', 'trash', 'btn-sm btn-icon') . btn('', 'info', 'eye', 'btn-sm btn-icon') . btn('', 'success', 'receipt', 'btn-sm btn-icon') . '</div>'];
}
$content = page_head('Service Management', 'Kelola data perbaikan perangkat pelanggan.')
    . '<div class="alert alert-success">' . ic('check') . '<span class="grow">Data service berhasil <b>ditambahkan</b>.</span>' . ic('x', 'icon icon-sm') . '</div>'
    . '<div class="card"><div class="card-body" style="flex-direction:row;justify-content:space-between;align-items:center">'
    .   btn('Tambah Service', 'primary', 'plus')
    .   '<div class="row gap-12">' . select('Semua Status') . '<div class="input-group" style="width:440px">' . input('Cari jenis kerusakan, deskripsi, atau pelanggan...', true) . btn('Cari', 'outline-secondary', 'search') . '</div></div>'
    . '</div></div>'
    . '<div class="card">'
    . table([['No', 56], ['Pelanggan', 150], ['Jenis Kerusakan', 170], ['Deskripsi', null], ['Status', 140], ['Tanggal Masuk', 170], ['Teknisi', 140], ['Aksi', 180]], $rows)
    . '<div class="card-body" style="flex-direction:row;justify-content:space-between;align-items:center;border-top:1px solid #e2e8f0"><div class="row gap-16 small muted"><span>Aksi:</span>'
    .   '<span class="row items-center gap-4">' . ic('edit', 'icon icon-sm') . ' Edit</span><span class="row items-center gap-4">' . ic('trash', 'icon icon-sm') . ' Hapus</span><span class="row items-center gap-4">' . ic('eye', 'icon icon-sm') . ' Detail</span><span class="row items-center gap-4">' . ic('receipt', 'icon icon-sm') . ' Buat Invoice</span></div>'
    . pagination(2, 1) . '</div>'
    . '</div>';
echo admin_frame('service', $content);
