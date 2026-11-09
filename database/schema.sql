-- ============================================================
-- Sistem Antrian Gudang - Struktur Database
-- Kompatibel MySQL 8 / MariaDB (InfinityFree)
-- Import di phpMyAdmin ke database yang sudah dibuat.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ------------------------------------------------------------
-- Tabel: admin (akun login)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: barang (master data barang)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `barang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` varchar(120) NOT NULL,
  `kategori` varchar(60) DEFAULT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `jumlah` int NOT NULL DEFAULT '0',
  `lokasi` varchar(60) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_barang` (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: antrian (antrian kendaraan muat/bongkar)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `antrian` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomor_antrian` varchar(10) NOT NULL,
  `driver` varchar(100) NOT NULL,
  `plat_nomor` varchar(20) NOT NULL,
  `perusahaan` varchar(100) NOT NULL,
  `jenis_kegiatan` enum('muat','bongkar') NOT NULL,
  `status` enum('menunggu','diproses','selesai') DEFAULT 'menunggu',
  `barang_diperiksa` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: antrian_barang (detail barang per antrian)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `antrian_barang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `antrian_id` int NOT NULL,
  `barang_id` int DEFAULT NULL,
  `kode_barang` varchar(30) DEFAULT NULL,
  `nama_barang` varchar(120) NOT NULL,
  `jumlah` int NOT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_antrian` (`antrian_id`),
  KEY `idx_barang` (`barang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: antrian_panggilan (panggilan terakhir per jalur)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `antrian_panggilan` (
  `jenis_kegiatan` enum('muat','bongkar') NOT NULL,
  `antrian_id` int NOT NULL,
  `dipanggil_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`jenis_kegiatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
