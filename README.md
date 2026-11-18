# FP Pemrograman Web  
_Aplikasi Sistem Service Laptop & Penjualan Produk_

---

## 📌 Deskripsi Proyek  
Proyek ini merupakan tugas akhir/“Final Project” untuk mata kuliah **Pemrograman Web**, yang dibangun menggunakan **PHP Native** dan MySQL. Proyek ini berfokus pada aplikasi sistem manajemen service laptop dan penjualan produk, tanpa menggunakan sistem POS penuh, melainkan menonjolkan fitur layanan, CRUD multi-role (admin, teknisi, pelanggan), manajemen data produk, layanan, invoice, dan riwayat status.

Tampilan website mengikuti desain prototype di Figma.

---

## 🎯 Fitur Utama  
- Sistem multi-role: **Admin**, **Teknisi**, dan **Pelanggan** dengan **satu halaman login** (diarahkan otomatis sesuai role)  
- CRUD kategori & produk, servis, pengguna (admin, teknisi, pelanggan dalam satu halaman dengan filter role), dan invoice  
- Tidak ada registrasi mandiri: akun pelanggan dibuatkan admin lewat menu Pengguna  
- Upload gambar produk & foto kerusakan (JPG, JPEG, PNG, GIF, WEBP)  
- Riwayat status service: setiap perubahan status/catatan teknisi tercatat dan bisa dilihat pelanggan  
- Tombol “Buat Invoice” hanya muncul jika service berstatus “selesai” dan belum memiliki invoice  
- Cetak invoice ke PDF (Dompdf)  
- Dashboard terpisah: admin (ringkasan toko), teknisi (tugas miliknya), pelanggan (riwayat servis)  

---

## 🚀 Instalasi di Laptop Sendiri

Kebutuhan: **PHP 8.1+** dan **MySQL 8 / MariaDB**. Paling mudah memakai Laragon atau XAMPP. Composer tidak wajib karena folder `vendor/` (Dompdf) sudah ikut di repo.

1. **Clone** ke folder web server, misalnya `C:\laragon\www` atau `C:\xampp\htdocs`:
   ```bash
   git clone https://github.com/SyahrulPermanaPutra/Vastacom2.git
   ```
2. **Nyalakan MySQL** (Laragon: *Start All*; XAMPP: *Start* Apache & MySQL).
3. **(Opsional) Setting database pribadi.** Bila user, password, atau port MySQL Anda berbeda dari bawaan (`root`, tanpa password, `3306`), salin `config/config.local.example.php` menjadi `config/config.local.php` lalu ubah isinya. File ini tidak ikut di-commit.
4. **Buat database + data awal**, pilih salah satu cara:
   - Dari terminal, di folder project:
     ```bash
     php database/install.php
     ```
   - Atau import `database/vastacom_db.sql` lewat HeidiSQL / phpMyAdmin. File ini otomatis membuat database `vastacom_db`.
5. Buka **http://localhost/Vastacom2/**. Nama folder bebas karena URL dideteksi otomatis.

**Akun demo** (password semua: `vastacom123`):

| Username | Role |
|---|---|
| `admin` | Admin |
| `teknisi1`, `teknisi2` | Teknisi |
| `budi`, `ani`, `rudi` | Pelanggan |

> ⚠️ `php database/install.php --force` dan import ulang `vastacom_db.sql` **menghapus seluruh data** di database tersebut lalu mengisinya kembali dengan data demo.

**Catatan untuk kontributor**
- Perubahan struktur tabel wajib ditulis juga di `database/vastacom_db.sql` agar anggota tim lain bisa mengikutinya.
- Gambar yang di-upload lewat aplikasi (`assets/image/`, `uploads/`) tidak ikut di-commit. Hanya gambar bawaan untuk data demo yang ada di repo.
- Jangan commit file berisi password atau `config/config.local.php`.

---

## 👥 Hak Akses

| Role | Akses |
|---|---|
| Pelanggan | Beranda, Dasbor Pelanggan (daftar servis, detail, riwayat status, invoice) |
| Teknisi | Panel: Dasbor & Servis (hanya servis yang ditugaskan; ubah status, catatan, foto) |
| Admin | Panel: Dasbor · Master Data (Kategori Produk, Produk) · Pengguna · Transaksi (Servis, Invoice) |

Login & logout untuk semua role melalui `index.php?page=login` / `index.php?page=logout`.

---

## 🗂️ Struktur Direktori

```
index.php               Router area publik & pelanggan (?page=home|login|dashboard|logout)
pages/                  Halaman publik: home, login, dashboard (pelanggan)
admin/index.php         Router panel admin & teknisi (?page=dashboard|kategori|produk|pengguna|service|invoice)
admin/pages/            Satu file per fitur panel (proses form + tampilan)
inc/layout.php          Head, navbar publik & panel, modal konfirmasi hapus
functions/              helpers.php (util, upload, pagination, CSRF), auth.php, service.php, ui.php (ikon SVG)
config/                 config.php (koneksi & BASE_URL otomatis), database.php (setting DB), config.local.example.php
database/               vastacom_db.sql (skema + data demo), install.php (installer CLI)
assets/css/design.css   Design system (sama dengan Figma)
assets/css/app.css      Penyesuaian web (responsif, form, modal)
assets/js/app.js        Modal, konfirmasi hapus, auto-submit filter
assets/image/           Gambar produk        uploads/   Foto kerusakan service
```

Setiap halaman memproses form lebih dulu lalu redirect (pola POST → Redirect → GET), sehingga refresh tidak mengirim ulang data. Semua form memakai token CSRF dan query memakai prepared statement.

---

## 🛠️ Teknologi yang Digunakan  
- Backend: **PHP Native** (8.x)  
- Basis data: **MySQL**  
- Frontend: HTML5, CSS3 (design system sendiri, font Inter, ikon SVG Lucide), JavaScript tanpa library  
- PDF: **Dompdf** (via Composer)  

---
