# FP Pemrograman Web  
_Aplikasi Sistem Service Laptop & Penjualan Produk_

---

## 📌 Deskripsi Proyek  
Proyek ini merupakan tugas akhir/“Final Project” untuk mata kuliah **Pemrograman Web**, yang dibangun menggunakan **PHP Native** dan MySQL. Proyek ini berfokus pada aplikasi sistem manajemen service laptop dan penjualan produk, tanpa menggunakan sistem POS penuh, melainkan menonjolkan fitur layanan, CRUD multi-role (admin, teknisi, pelanggan), manajemen data produk, layanan, invoice, dan riwayat status.

Tampilan website dibuat sama persis dengan desain Figma di folder [`figma-design/`](figma-design/README.md).

---

## 🎯 Fitur Utama  
- Sistem multi-role: **Admin**, **Teknisi**, dan **Pelanggan** dengan **satu halaman login** (diarahkan otomatis sesuai role)  
- CRUD data produk (dengan kategori), service, pelanggan, user, dan invoice  
- Upload gambar produk & foto kerusakan (JPG, JPEG, PNG, GIF, WEBP)  
- Riwayat status service: setiap perubahan status/catatan teknisi tercatat dan bisa dilihat pelanggan  
- Tombol “Buat Invoice” hanya muncul jika service berstatus “selesai” dan belum memiliki invoice  
- Cetak invoice ke PDF (Dompdf)  
- Dashboard terpisah: admin (ringkasan toko), teknisi (tugas miliknya), pelanggan (riwayat servis)  

---

## 👥 Hak Akses

| Role | Akses |
|---|---|
| Pelanggan | Home, Dashboard Pelanggan (daftar service, detail, riwayat status, invoice) |
| Teknisi | Panel: Dashboard & Service (hanya service yang ditugaskan; ubah status, catatan, foto) |
| Admin | Panel: Dashboard, Produk, Kategori, Users, Pelanggan, Service, Invoice |

Login & logout untuk semua role melalui `index.php?page=login` / `index.php?page=logout`.

---

## 🗂️ Struktur Direktori

```
index.php               Router area publik & pelanggan (?page=home|login|register|dashboard|logout)
pages/                  Halaman publik: home, login, register, dashboard (pelanggan)
admin/index.php         Router panel admin & teknisi (?page=dashboard|produk|kategori|users|pelanggan|service|invoice)
admin/pages/            Satu file per fitur panel (proses form + tampilan)
inc/layout.php          Head, navbar publik & panel, modal konfirmasi hapus
functions/              helpers.php (util, upload, pagination, CSRF), auth.php, service.php, ui.php (ikon SVG)
config/config.php       Koneksi database & deteksi BASE_URL otomatis
assets/css/design.css   Design system (sama dengan Figma)
assets/css/app.css      Penyesuaian web (responsif, form, modal)
assets/js/app.js        Modal, konfirmasi hapus, auto-submit filter
assets/image/           Gambar produk        uploads/   Foto kerusakan service
figma-design/           Sumber & hasil HTML untuk Figma (code.to.design)
```

Setiap halaman memproses form lebih dulu lalu redirect (pola POST → Redirect → GET), sehingga refresh tidak mengirim ulang data. Semua form memakai token CSRF dan query memakai prepared statement.

---

## 🛠️ Teknologi yang Digunakan  
- Backend: **PHP Native** (8.x)  
- Basis data: **MySQL**  
- Frontend: HTML5, CSS3 (design system sendiri, font Inter, ikon SVG Lucide), JavaScript tanpa library  
- PDF: **Dompdf** (via Composer)  

---
