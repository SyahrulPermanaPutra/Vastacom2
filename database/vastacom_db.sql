-- =====================================================================
-- Vastacom — skema database + data awal (demo)
-- Import lewat:  php database/install.php   (disarankan)
--          atau: HeidiSQL / phpMyAdmin → import file ini
--          atau: mysql -u root < database/vastacom_db.sql
--
-- PERHATIAN: tabel lama dengan nama yang sama akan DIHAPUS lalu dibuat ulang.
--
-- Akun demo (semua password: vastacom123) — ganti setelah dipakai di server:
--   admin     → admin
--   teknisi1  → teknisi
--   teknisi2  → teknisi
--   budi      → pelanggan
--   ani       → pelanggan
--   rudi      → pelanggan
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `vastacom_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `vastacom_db`;

DROP TABLE IF EXISTS `notifikasi`, `booking`, `invoice`, `history_status`, `service`, `produk`, `product_category`, `pelanggan`, `users`;

-- ---------------------------------------------------------------------
-- Struktur tabel
-- ---------------------------------------------------------------------

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','teknisi','pelanggan') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pelanggan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `product_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `produk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catg_id` int DEFAULT NULL,
  `status` enum('0','1') COLLATE utf8mb4_general_ci DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_catg_id` (`catg_id`),
  CONSTRAINT `fk_catg_id` FOREIGN KEY (`catg_id`) REFERENCES `product_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `service` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int NOT NULL,
  `id_teknisi` int DEFAULT NULL,
  `jenis_kerusakan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `status` enum('menunggu','diproses','selesai','dibatalkan') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'menunggu',
  `tanggal_masuk` datetime NOT NULL,
  `tanggal_update` datetime DEFAULT NULL,
  `foto_kerusakan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_service_pelanggan` (`id_pelanggan`),
  KEY `fk_service_teknisi` (`id_teknisi`),
  CONSTRAINT `fk_service_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id`),
  CONSTRAINT `fk_service_teknisi` FOREIGN KEY (`id_teknisi`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `history_status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `status` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` int DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_history_status_service` (`service_id`),
  KEY `fk_history_status_user` (`updated_by`),
  CONSTRAINT `fk_history_status_service` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`),
  CONSTRAINT `fk_history_status_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `invoice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `tanggal_invoice` datetime NOT NULL,
  `total_biaya` decimal(10,2) NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `metode_pembayaran` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_pembayaran` enum('pending','lunas','batal') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `tanggal_pembayaran` datetime DEFAULT NULL,
  `id_admin_pembuat` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_invoice_service` (`service_id`),
  KEY `fk_invoice_admin` (`id_admin_pembuat`),
  CONSTRAINT `fk_invoice_admin` FOREIGN KEY (`id_admin_pembuat`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_invoice_service` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel cadangan untuk pengembangan berikutnya (belum dipakai di UI)
CREATE TABLE `booking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pelanggan_id` int NOT NULL,
  `id_teknisi` int DEFAULT NULL,
  `tanggal_booking` datetime NOT NULL,
  `status` enum('pending','diterima','batal') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `catatan_pelanggan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_booking_pelanggan` (`pelanggan_id`),
  KEY `fk_booking_teknisi` (`id_teknisi`),
  CONSTRAINT `fk_booking_pelanggan` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  CONSTRAINT `fk_booking_teknisi` FOREIGN KEY (`id_teknisi`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifikasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `jenis` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `isi` text COLLATE utf8mb4_general_ci NOT NULL,
  `link_detail` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notifikasi_service` (`service_id`),
  KEY `fk_notifikasi_user` (`user_id`),
  CONSTRAINT `fk_notifikasi_service` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`),
  CONSTRAINT `fk_notifikasi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Data awal (demo)
-- ---------------------------------------------------------------------

-- Password semua akun: vastacom123
INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin',    '$2y$10$C3cQYu0ti3OqVCNB2w35wep3MoUCjBr3Z2Oe5wk2h1Lot0wR1hdW6', 'admin'),
(2, 'teknisi1', '$2y$10$C3cQYu0ti3OqVCNB2w35wep3MoUCjBr3Z2Oe5wk2h1Lot0wR1hdW6', 'teknisi'),
(3, 'teknisi2', '$2y$10$C3cQYu0ti3OqVCNB2w35wep3MoUCjBr3Z2Oe5wk2h1Lot0wR1hdW6', 'teknisi'),
(4, 'budi',     '$2y$10$C3cQYu0ti3OqVCNB2w35wep3MoUCjBr3Z2Oe5wk2h1Lot0wR1hdW6', 'pelanggan'),
(5, 'ani',      '$2y$10$C3cQYu0ti3OqVCNB2w35wep3MoUCjBr3Z2Oe5wk2h1Lot0wR1hdW6', 'pelanggan'),
(6, 'rudi',     '$2y$10$C3cQYu0ti3OqVCNB2w35wep3MoUCjBr3Z2Oe5wk2h1Lot0wR1hdW6', 'pelanggan');

INSERT INTO `pelanggan` (`id`, `user_id`, `nama`, `email`, `no_hp`) VALUES
(1, 4, 'Budi Santoso', 'budi@example.com', '081234567890'),
(2, 5, 'Ani Wijaya',   'ani@example.com',  '081298765432'),
(3, 6, 'Rudi Hartono', 'rudi@example.com', NULL);

INSERT INTO `product_category` (`id`, `nama`) VALUES
(1, 'Laptop'),
(2, 'Hardware'),
(3, 'Aksesoris');

-- Produk tanpa gambar memakai assets/image/default.jpg
INSERT INTO `produk` (`id`, `nama`, `harga`, `gambar`, `catg_id`, `status`) VALUES
(1, 'Lenovo IdeaPad Slim 3',         7899000.00, 'lenovo-ideapad.jpg', 1, '1'),
(2, 'Dell Latitude 3420',            8999000.00, 'dell-latitude.jpeg', 1, '1'),
(3, 'Acer Nitro V15',               11000000.00, NULL,                 1, '1'),
(4, 'HP Victus 15',                  9680000.00, NULL,                 1, '1'),
(5, 'SSD Samsung 970 EVO Plus 500GB',1150000.00, NULL,                 2, '1'),
(6, 'RAM Kingston 8GB DDR4',          450000.00, NULL,                 2, '1'),
(7, 'Mouse Logitech M331',            219000.00, NULL,                 3, '1'),
(8, 'Keyboard Logitech K380',         525000.00, NULL,                 3, '0');

INSERT INTO `service` (`id`, `id_pelanggan`, `id_teknisi`, `jenis_kerusakan`, `deskripsi`, `status`, `tanggal_masuk`, `tanggal_update`, `foto_kerusakan`) VALUES
(1, 1, 2,    'Laptop tidak menyala',          'Laptop mati total setelah terkena air, indikator charger tidak menyala.', 'selesai',  '2026-11-02 09:15:00', '2026-11-06 15:00:00', NULL),
(2, 1, 3,    'Ganti layar retak',             'Layar retak di pojok kanan bawah dan muncul garis vertikal.',              'diproses', '2026-11-14 10:30:00', '2026-11-15 09:00:00', NULL),
(3, 2, 2,    'Instal ulang Windows & Office', 'Instal ulang Windows 11 dan Microsoft Office, backup data di drive D.',     'menunggu', '2026-11-17 13:00:00', '2026-11-17 13:00:00', NULL),
(4, 3, NULL, 'Keyboard beberapa tombol mati', 'Tombol A, S, dan D tidak berfungsi.',                                      'menunggu', '2026-11-18 08:45:00', '2026-11-18 08:45:00', NULL),
(5, 2, 3,    'Laptop cepat panas (overheat)', 'Laptop mati sendiri saat dipakai bermain game.',                           'selesai',  '2026-10-20 11:00:00', '2026-10-24 14:00:00', NULL);

INSERT INTO `history_status` (`service_id`, `status`, `updated_on`, `updated_by`, `catatan`) VALUES
(1, 'menunggu', '2026-11-02 09:15:00', 1, 'Perangkat diterima di toko.'),
(1, 'diproses', '2026-11-03 10:00:00', 2, 'Pengecekan jalur power, IC power terindikasi rusak.'),
(1, 'selesai',  '2026-11-06 15:00:00', 2, 'IC power diganti, laptop menyala normal.'),
(2, 'menunggu', '2026-11-14 10:30:00', 1, 'Perangkat diterima di toko.'),
(2, 'diproses', '2026-11-15 09:00:00', 3, 'Menunggu panel LCD pengganti dari supplier.'),
(3, 'menunggu', '2026-11-17 13:00:00', 1, 'Perangkat diterima di toko.'),
(4, 'menunggu', '2026-11-18 08:45:00', 1, 'Perangkat diterima di toko.'),
(5, 'menunggu', '2026-10-20 11:00:00', 1, 'Perangkat diterima di toko.'),
(5, 'diproses', '2026-10-21 09:30:00', 3, 'Pembersihan kipas dan penggantian thermal paste.'),
(5, 'selesai',  '2026-10-24 14:00:00', 3, 'Suhu normal setelah pengujian 1 jam.');

INSERT INTO `invoice` (`service_id`, `tanggal_invoice`, `total_biaya`, `keterangan`, `metode_pembayaran`, `status_pembayaran`, `tanggal_pembayaran`, `id_admin_pembuat`) VALUES
(1, '2026-11-06 16:00:00', 450000.00, 'Penggantian IC power',              'Transfer Bank', 'pending', NULL,                  1),
(5, '2026-10-24 15:00:00', 250000.00, 'Cleaning kipas & ganti thermal paste', 'Tunai',      'lunas',   '2026-10-24 15:30:00', 1);

SET FOREIGN_KEY_CHECKS = 1;
