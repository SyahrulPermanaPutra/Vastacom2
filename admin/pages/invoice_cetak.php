<?php
// Cetak invoice ke PDF (Figma: 22). Dompdf tidak mendukung flexbox, jadi layout memakai tabel.
require_once ROOT_PATH . '/vendor/autoload.php';

use Dompdf\Dompdf;

$inv = db_one($conn, "SELECT i.*, s.jenis_kerusakan, s.deskripsi, p.nama AS nama_pelanggan, p.email, p.no_hp, u.username AS pembuat
                      FROM invoice i JOIN service s ON i.service_id = s.id JOIN pelanggan p ON s.id_pelanggan = p.id
                      LEFT JOIN users u ON i.id_admin_pembuat = u.id WHERE i.id = ?", 'i', (int)($_GET['id'] ?? 0));
if (!$inv) {
    flash('danger', 'Invoice tidak ditemukan.');
    redirect('admin/index.php?page=invoice');
}

[$statusLabel] = STATUS_BAYAR[$inv['status_pembayaran']];
$statusColor = ['pending' => '#8a6100;background:#fff3cd', 'lunas' => '#0f5132;background:#d1e7dd', 'batal' => '#842029;background:#f8d7da'][$inv['status_pembayaran']];
$kontak = trim(($inv['email'] ?: '') . ($inv['email'] && $inv['no_hp'] ? ' · ' : '') . ($inv['no_hp'] ?: ''));

$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
  @page { margin: 48px 56px; }
  body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 12px; }
  table { width: 100%; border-collapse: collapse; }
  .muted { color: #64748b; } .label { color: #64748b; font-size: 10px; font-weight: bold; letter-spacing: .5px; }
  .brand { font-size: 18px; font-weight: bold; color: #212529; }
  .title { font-size: 28px; font-weight: bold; color: #0d6efd; letter-spacing: 2px; text-align: right; }
  .line { border-top: 1px solid #e2e8f0; margin: 18px 0; }
  .items th { background: #f8fafc; text-align: left; font-size: 10px; color: #475569; padding: 10px 12px; border-bottom: 1px solid #cbd5e1; }
  .items td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
  .total td { background: #f8fafc; font-weight: bold; font-size: 14px; }
  .badge { padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; }
</style></head><body>
<table><tr>
  <td><div class="brand">Vasta Computer</div><div class="muted" style="width:260px;margin-top:4px">Jl. Granit Nila No.20A, Driyorejo, Gresik, Jawa Timur 61177 · 0815-5409-1512</div></td>
  <td style="text-align:right;vertical-align:top"><div class="title">INVOICE</div><div style="font-weight:bold">INV-' . (int)$inv['id'] . '</div><div class="muted">Tanggal: ' . date('d-m-Y', strtotime($inv['tanggal_invoice'])) . '</div></td>
</tr></table>
<div class="line"></div>
<table><tr>
  <td><div class="label">DITAGIHKAN KEPADA</div><div style="font-size:14px;font-weight:bold;margin-top:4px">' . e($inv['nama_pelanggan']) . '</div><div class="muted">' . e($kontak) . '</div></td>
  <td style="text-align:right;vertical-align:top"><div class="label">STATUS PEMBAYARAN</div><div style="margin-top:6px"><span class="badge" style="color:' . $statusColor . '">' . $statusLabel . '</span></div></td>
</tr></table>
<div style="margin-top:20px"><div class="label">JENIS KERUSAKAN</div><div style="font-weight:bold;margin-top:4px">' . e($inv['jenis_kerusakan']) . '</div><div class="muted">' . nl2br(e($inv['deskripsi'])) . '</div></div>
<table class="items" style="margin-top:20px">
  <tr><th>ITEM</th><th style="text-align:right">BIAYA</th></tr>
  <tr><td>Perbaikan — ' . e($inv['jenis_kerusakan']) . ($inv['keterangan'] ? '<div class="muted">' . nl2br(e($inv['keterangan'])) . '</div>' : '') . '</td><td style="text-align:right">' . rp($inv['total_biaya']) . '</td></tr>
  <tr class="total"><td>Total</td><td style="text-align:right;color:#0d6efd">' . rp($inv['total_biaya']) . '</td></tr>
</table>
<table style="margin-top:20px"><tr>
  <td><div class="label">METODE PEMBAYARAN</div><div style="margin-top:4px">' . e($inv['metode_pembayaran'] ?: '-') . '</div></td>
  <td><div class="label">TANGGAL PEMBAYARAN</div><div style="margin-top:4px">' . ($inv['tanggal_pembayaran'] ? date('d-m-Y', strtotime($inv['tanggal_pembayaran'])) : '-') . '</div></td>
  <td><div class="label">DIBUAT OLEH</div><div style="margin-top:4px">' . e($inv['pembuat'] ?: '-') . '</div></td>
</tr></table>
<div class="line" style="margin-top:40px"></div>
<div class="muted" style="text-align:center">Terima kasih telah mempercayakan perangkat Anda kepada Vasta Computer. Garansi servis 90 hari.</div>
</body></html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();
$dompdf->stream('invoice-' . (int)$inv['id'] . '.pdf', ['Attachment' => false]);
