# Figma Design — Vasta Computer

Halaman HTML + CSS statis dari semua page dan fitur aplikasi, siap di-paste ke plugin Figma **code.to.design**.

## Cara pakai

1. Buka `html/<nama-halaman>.html` di editor (VS Code), lalu salin **seluruh isi file** (Ctrl+A, Ctrl+C).
2. Di Figma, jalankan plugin **code.to.design**, pilih mode **HTML**, paste, lalu klik import.
3. Ulangi untuk halaman lain. Satu file menghasilkan satu frame dengan lebar 1440 px.

Untuk melihat semua halaman sekaligus, buka `http://localhost/vastacom/figma-design/html/` (Laragon).

## Daftar halaman

| File | Isi |
|---|---|
| 00-design-system | Warna, tipografi, tombol, form, badge, alert, pagination, tabel |
| 01-home | Landing page: hero, layanan, produk, keunggulan, statistik, kontak & peta |
| 02-login / 03-registrasi | Login untuk semua role (pelanggan, teknisi, admin) & registrasi pelanggan |
| 04-dashboard-pelanggan | Daftar service milik pelanggan |
| 05-dashboard-pelanggan-detail | Modal detail service, timeline status & invoice |
| 07-admin-dashboard | Kartu statistik, service terbaru, grafik donut, invoice terbaru |
| 08 / 09 | Data Produk + modal tambah & edit |
| 10 / 11 | Kategori Produk + modal tambah & edit |
| 12 / 13 | Data User + modal tambah & edit user |
| 14 / 15 | Data Pelanggan + modal tambah & edit |
| 16 / 17 / 18 / 19 | Service Management, modal form, modal detail (komponen sama dengan 05), konfirmasi hapus & buat invoice |
| 20 / 21 | Manajemen Invoice + modal tambah & edit |
| 22-invoice-cetak | Dokumen invoice PDF (A4) |

## Mengubah desain

Jangan mengedit file di folder `html/` secara langsung, karena isinya akan tertimpa saat build. Ubah sumbernya:

- `../assets/css/design.css` — design system (warna, ukuran, komponen), dipakai juga oleh website
- `src/lib.php` — navbar, tabel, form, modal, dan data contoh (ikon SVG dari `../functions/ui.php`)
- `src/pages/*.php` — isi setiap halaman

Lalu build ulang:

```bash
php figma-design/build.php
```

Catatan: semua layout memakai flexbox (menjadi Auto Layout di Figma), ikon berupa SVG inline (menjadi vector), dan tidak ada JavaScript. Data di halaman adalah contoh statis.
