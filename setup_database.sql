-- =====================================================
-- SETUP DATABASE LENGKAP - APLIKASI DARURAT
-- =====================================================
-- File ini akan membuat database, tabel, dan data
-- dari awal untuk aplikasi emergency system
-- 
-- Cara penggunaan:
-- 1. Buka phpMyAdmin
-- 2. Klik tab "SQL" atau "Import"
-- 3. Copy-paste atau upload file ini
-- 4. Klik "Go" / "Execute"
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- 1. BUAT DATABASE (jika belum ada)
-- =====================================================
CREATE DATABASE IF NOT EXISTS `emergency_system` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `emergency_system`;

-- =====================================================
-- 2. HAPUS TABEL LAMA (jika ada) untuk fresh install
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `report_media`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `log_admin`;
DROP TABLE IF EXISTS `admin`;
DROP TABLE IF EXISTS `alamat_instansi`;
DROP TABLE IF EXISTS `instansi`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 3. BUAT TABEL: instansi
-- =====================================================
CREATE TABLE `instansi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) NOT NULL COMMENT 'Nama instansi/organisasi',
  `kode` varchar(50) NOT NULL COMMENT 'Kode unik instansi',
  `jenis` enum('pemerintah','swasta','nirlaba','lainnya') DEFAULT 'pemerintah' COMMENT 'Jenis instansi',
  `status` enum('active','inactive','suspended') DEFAULT 'active' COMMENT 'Status instansi',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `idx_kode` (`kode`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data instansi/organisasi';

-- =====================================================
-- 4. BUAT TABEL: alamat_instansi
-- =====================================================
CREATE TABLE `alamat_instansi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instansi_id` int(11) NOT NULL COMMENT 'ID instansi (1:1 relationship)',
  `alamat_lengkap` text NOT NULL COMMENT 'Alamat lengkap',
  `kelurahan` varchar(100) DEFAULT NULL COMMENT 'Kelurahan',
  `kecamatan` varchar(100) DEFAULT NULL COMMENT 'Kecamatan',
  `kota` varchar(100) NOT NULL COMMENT 'Kota/Kabupaten',
  `provinsi` varchar(100) NOT NULL COMMENT 'Provinsi',
  `kode_pos` varchar(10) DEFAULT NULL COMMENT 'Kode pos',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Koordinat latitude instansi',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Koordinat longitude instansi',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir',
  PRIMARY KEY (`id`),
  UNIQUE KEY `instansi_id` (`instansi_id`),
  KEY `idx_instansi_id` (`instansi_id`),
  KEY `idx_kota` (`kota`),
  KEY `idx_provinsi` (`provinsi`),
  KEY `idx_coordinates` (`latitude`,`longitude`),
  CONSTRAINT `alamat_instansi_ibfk_1` FOREIGN KEY (`instansi_id`) REFERENCES `instansi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel alamat instansi (1:1 dengan instansi)';

-- =====================================================
-- 5. BUAT TABEL: users
-- =====================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT 'Username untuk login',
  `email` varchar(100) NOT NULL COMMENT 'Email pengguna',
  `password` varchar(255) NOT NULL COMMENT 'Password ter-hash',
  `fullname` varchar(200) NOT NULL COMMENT 'Nama lengkap pengguna',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Nomor telepon (opsional)',
  `status` enum('active','inactive','suspended') DEFAULT 'active' COMMENT 'Status akun pengguna',
  `google_id` varchar(100) DEFAULT NULL COMMENT 'Google OAuth ID',
  `remember_token` varchar(255) DEFAULT NULL COMMENT 'Token untuk remember me',
  `remember_expiry` int(11) DEFAULT NULL COMMENT 'Expiry timestamp untuk remember token',
  `last_login` datetime DEFAULT NULL COMMENT 'Waktu login terakhir',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_google_id` (`google_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data pengguna (masyarakat)';

-- =====================================================
-- 6. BUAT TABEL: admin
-- =====================================================
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instansi_id` int(11) NOT NULL COMMENT 'ID instansi tempat admin bekerja',
  `username` varchar(50) NOT NULL COMMENT 'Username untuk login',
  `email` varchar(100) NOT NULL COMMENT 'Email admin',
  `password` varchar(255) NOT NULL COMMENT 'Password ter-hash',
  `fullname` varchar(200) NOT NULL COMMENT 'Nama lengkap admin',
  `role` enum('super_admin','admin','operator') DEFAULT 'admin' COMMENT 'Role admin',
  `status` enum('active','inactive','suspended') DEFAULT 'active' COMMENT 'Status akun admin',
  `last_login` datetime DEFAULT NULL COMMENT 'Waktu login terakhir',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_instansi_id` (`instansi_id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_role` (`role`),
  CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`instansi_id`) REFERENCES `instansi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data admin yang terhubung ke instansi';

-- =====================================================
-- 7. BUAT TABEL: reports
-- =====================================================
CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID pengguna yang membuat laporan',
  `title` varchar(200) NOT NULL COMMENT 'Judul laporan',
  `category` enum('kecelakaan','kebakaran','medis','kejahatan','bencana','lainnya') NOT NULL COMMENT 'Kategori kejadian',
  `description` text NOT NULL COMMENT 'Deskripsi lengkap kejadian',
  `location` varchar(255) NOT NULL COMMENT 'Lokasi kejadian (alamat)',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Koordinat latitude (untuk GPS)',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Koordinat longitude (untuk GPS)',
  `urgent` tinyint(1) DEFAULT 0 COMMENT 'Flag darurat (1=urgent, 0=normal)',
  `status` enum('pending','processing','dispatched','completed','cancelled') DEFAULT 'pending' COMMENT 'Status laporan',
  `admin_notes` text DEFAULT NULL COMMENT 'Catatan dari admin (dari sistem dispatch)',
  `dispatched_to` varchar(200) DEFAULT NULL COMMENT 'Instansi yang ditugaskan (dari sistem dispatch)',
  `dispatched_at` datetime DEFAULT NULL COMMENT 'Waktu dispatch ke instansi',
  `completed_at` datetime DEFAULT NULL COMMENT 'Waktu laporan selesai',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  KEY `idx_urgent` (`urgent`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_location` (`latitude`,`longitude`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel laporan darurat dari pengguna';

-- =====================================================
-- 8. BUAT TABEL: report_media
-- =====================================================
CREATE TABLE `report_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL COMMENT 'ID laporan yang memiliki media ini',
  `file_path` varchar(500) NOT NULL COMMENT 'Path file di server',
  `file_type` varchar(50) DEFAULT NULL COMMENT 'Tipe file',
  `file_size` int(11) DEFAULT NULL COMMENT 'Ukuran file dalam bytes',
  `file_name` varchar(255) DEFAULT NULL COMMENT 'Nama file asli',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu upload',
  PRIMARY KEY (`id`),
  KEY `idx_report_id` (`report_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `report_media_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel media/foto yang dilampirkan pada laporan (opsional)';

-- =====================================================
-- 9. BUAT TABEL: log_admin
-- =====================================================
CREATE TABLE `log_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL COMMENT 'ID admin yang melakukan aksi (NULL jika admin sudah dihapus)',
  `action` varchar(100) NOT NULL COMMENT 'Jenis aksi',
  `description` text DEFAULT NULL COMMENT 'Deskripsi detail aksi',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address admin',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'User agent browser',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu aksi dilakukan',
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `log_admin_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log aktivitas admin untuk audit trail (opsional)';

-- =====================================================
-- 10. INSERT DATA: instansi
-- =====================================================
INSERT INTO `instansi` (`id`, `nama`, `kode`, `jenis`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Dinas Pemadam Kebakaran Kota Jakarta', 'DPK-JKT-001', 'pemerintah', 'active', '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(2, 'Rumah Sakit Umum Daerah', 'RSUD-001', 'pemerintah', 'active', '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(3, 'Polisi Resor Jakarta Selatan', 'POLRES-JAKSEL', 'pemerintah', 'active', '2025-12-19 13:39:10', '2025-12-19 13:39:10');

-- =====================================================
-- 11. INSERT DATA: alamat_instansi
-- =====================================================
INSERT INTO `alamat_instansi` (`id`, `instansi_id`, `alamat_lengkap`, `kelurahan`, `kecamatan`, `kota`, `provinsi`, `kode_pos`, `created_at`, `updated_at`, `latitude`, `longitude`) VALUES
(1, 1, 'Jl. Jend. Sudirman No. 1', 'Kebon Melati', 'Tanah Abang', 'Jakarta Pusat', 'DKI Jakarta', '10220', '2025-12-19 13:39:10', '2025-12-19 13:39:10', NULL, NULL),
(2, 2, 'Jl. Dr. Sutomo No. 8', 'Pasar Baru', 'Sawah Besar', 'Jakarta Pusat', 'DKI Jakarta', '10710', '2025-12-19 13:39:10', '2025-12-19 13:39:10', NULL, NULL),
(3, 3, 'Jl. Wijaya II No. 1', 'Petogogan', 'Kebayoran Baru', 'Jakarta Selatan', 'DKI Jakarta', '12170', '2025-12-19 13:39:10', '2025-12-19 13:39:10', NULL, NULL);

-- =====================================================
-- 12. INSERT DATA: users
-- Password default: "password123" (sudah di-hash)
-- =====================================================
INSERT INTO `users` (`id`, `username`, `email`, `password`, `fullname`, `phone`, `status`, `google_id`, `remember_token`, `remember_expiry`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'john_doe', 'john.doe@example.com', '$2y$10$vxBJSTmULtP4uNBKXTWfme.4jMEGwn2LWkrtTZqRGdauXkbxxWG16', 'John Doe', '081234567890', 'active', NULL, NULL, NULL, NULL, '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(2, 'jane_smith', 'jane.smith@example.com', '$2y$10$vxBJSTmULtP4uNBKXTWfme.4jMEGwn2LWkrtTZqRGdauXkbxxWG16', 'Jane Smith', '081234567891', 'active', NULL, NULL, NULL, NULL, '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(3, 'budi_santoso', 'budi.santoso@example.com', '$2y$10$vxBJSTmULtP4uNBKXTWfme.4jMEGwn2LWkrtTZqRGdauXkbxxWG16', 'Budi Santoso', '081234567892', 'active', NULL, NULL, NULL, NULL, '2025-12-19 13:39:10', '2025-12-19 13:39:10');

-- =====================================================
-- 13. INSERT DATA: admin
-- Password default: "admin123" (sudah di-hash)
-- =====================================================
INSERT INTO `admin` (`id`, `instansi_id`, `username`, `email`, `password`, `fullname`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin_dpk', 'admin.dpk@jakarta.go.id', '$2y$10$UB5Eqfn/sN/0B418toohnOf4K47nEioVrqp0KCVGwCcKhAn1mcngW', 'Admin DPK Jakarta', 'admin', 'active', NULL, '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(2, 2, 'admin_rsud', 'admin.rsud@jakarta.go.id', '$2y$10$UB5Eqfn/sN/0B418toohnOf4K47nEioVrqp0KCVGwCcKhAn1mcngW', 'Admin RSUD', 'admin', 'active', NULL, '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(3, 3, 'admin_polres', 'admin.polres@jakarta.go.id', '$2y$10$UB5Eqfn/sN/0B418toohnOf4K47nEioVrqp0KCVGwCcKhAn1mcngW', 'Admin Polres Jaksel', 'admin', 'active', NULL, '2025-12-19 13:39:10', '2025-12-19 13:39:10');

-- =====================================================
-- 14. INSERT DATA: reports (sample data)
-- =====================================================
INSERT INTO `reports` (`id`, `user_id`, `title`, `category`, `description`, `location`, `latitude`, `longitude`, `urgent`, `status`, `admin_notes`, `dispatched_to`, `dispatched_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kebakaran Rumah Tinggal di Jl. Sudirman', 'kebakaran', 'Terjadi kebakaran di rumah 2 lantai, api sudah mulai membesar. Diperlukan bantuan pemadam kebakaran segera.', 'Jl. Sudirman No. 45, Jakarta Pusat', -6.20880000, 106.84560000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 19:40:17', '2026-01-04 19:40:17'),
(2, 1, 'Kebakaran Gedung Perkantoran', 'kebakaran', 'Asap tebal keluar dari lantai 3 gedung perkantoran. Evakuasi sedang berlangsung.', 'Jl. Thamrin No. 10, Jakarta Pusat', -6.19440000, 106.82290000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 17:40:17', '2026-01-04 17:40:17'),
(3, 2, 'Kecelakaan Lalu Lintas di Perempatan', 'kecelakaan', 'Tabrakan antara mobil dan motor di perempatan. Korban membutuhkan bantuan medis.', 'Jl. Gatot Subroto, Jakarta Selatan', -6.22970000, 106.79890000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 18:40:17', '2026-01-04 19:40:17'),
(4, 2, 'Kecelakaan Mobil Tabrakan dengan Truk', 'kecelakaan', 'Kecelakaan serius di jalan tol. Beberapa korban luka-luka membutuhkan ambulans.', 'Jl. Tol Jagorawi Km 10', -6.25000000, 106.90000000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 16:40:17', '2026-01-04 19:40:17'),
(5, 2, 'Kecelakaan Sepeda Motor', 'kecelakaan', 'Kecelakaan tunggal sepeda motor. Pengendara terjatuh dan mengalami luka di tangan.', 'Jl. Kebayoran Baru, Jakarta Selatan', -6.24330000, 106.79950000, 0, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 14:40:17', '2026-01-04 19:40:17'),
(6, 3, 'Kebutuhan Ambulans Medis', 'medis', 'Warga membutuhkan ambulans untuk evakuasi korban sakit. Kondisi korban cukup parah.', 'Jl. Senopati, Jakarta Selatan', -6.24440000, 106.80050000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 19:10:17', '2026-01-04 19:40:17'),
(7, 1, 'Darurat Medis di Rumah', 'medis', 'Pasien pingsan dan membutuhkan bantuan medis segera. Keluarga meminta ambulans.', 'Jl. Cipete Raya, Jakarta Selatan', -6.26000000, 106.81000000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 18:55:17', '2026-01-04 19:40:17'),
(8, 2, 'Kecelakaan dengan Korban Luka', 'medis', 'Ada korban luka akibat kecelakaan. Diperlukan pertolongan medis segera.', 'Jl. Rasuna Said, Jakarta Selatan', -6.23800000, 106.83000000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 17:40:17', '2026-01-04 19:40:17'),
(9, 3, 'Pencurian Kendaraan Bermotor', 'kejahatan', 'Motor hilang dicuri di depan rumah. Sudah dilaporkan ke polisi setempat.', 'Jl. Kemang Raya, Jakarta Selatan', -6.25800000, 106.80800000, 0, 'pending', NULL, NULL, NULL, NULL, '2026-01-03 19:40:17', '2026-01-04 19:40:17'),
(10, 1, 'Perampokan di Jalan Raya', 'kejahatan', 'Terjadi perampokan di jalan raya. Korban kehilangan barang berharga. Diperlukan patroli polisi.', 'Jl. Kemang Timur, Jakarta Selatan', -6.25700000, 106.80900000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 15:40:17', '2026-01-04 19:40:17'),
(11, 2, 'Banjir di Permukiman', 'bencana', 'Kawasan permukiman terendam banjir setinggi 50cm. Warga membutuhkan bantuan evakuasi.', 'Jl. Cilandak, Jakarta Selatan', -6.27000000, 106.78000000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-02 19:40:17', '2026-01-04 19:40:17'),
(12, 3, 'Pohon Tumbang Menutup Jalan', 'bencana', 'Pohon besar tumbang akibat hujan deras dan menutup jalan. Lalu lintas terhambat.', 'Jl. Fatmawati, Jakarta Selatan', -6.24500000, 106.79500000, 0, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 13:40:17', '2026-01-04 19:40:17'),
(13, 1, 'Gangguan Listrik di Perumahan', 'lainnya', 'Listrik padam di seluruh perumahan. Warga membutuhkan bantuan PLN untuk perbaikan.', 'Jl. Lebak Bulus, Jakarta Selatan', -6.29000000, 106.77000000, 0, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 16:40:17', '2026-01-04 19:40:17'),
(14, 2, 'Kerusakan Jalan Berlubang', 'lainnya', 'Jalan berlubang besar di tengah jalan. Berbahaya untuk pengendara. Diperlukan perbaikan.', 'Jl. TB Simatupang, Jakarta Selatan', -6.30000000, 106.80000000, 0, 'pending', NULL, NULL, NULL, NULL, '2026-01-03 19:40:17', '2026-01-04 19:40:17');

-- =====================================================
-- 15. RESET AUTO_INCREMENT
-- =====================================================
ALTER TABLE `admin` AUTO_INCREMENT = 4;
ALTER TABLE `alamat_instansi` AUTO_INCREMENT = 4;
ALTER TABLE `instansi` AUTO_INCREMENT = 4;
ALTER TABLE `log_admin` AUTO_INCREMENT = 1;
ALTER TABLE `reports` AUTO_INCREMENT = 15;
ALTER TABLE `report_media` AUTO_INCREMENT = 1;
ALTER TABLE `users` AUTO_INCREMENT = 4;

-- =====================================================
-- SELESAI - Database siap digunakan!
-- =====================================================
-- 
-- CREDENTIALS DEFAULT:
-- 
-- ADMIN:
-- - Username: admin_dpk
-- - Email: admin.dpk@jakarta.go.id
-- - Password: admin123
-- 
-- USER:
-- - Username: john_doe
-- - Email: john.doe@example.com
-- - Password: password123
-- 
-- =====================================================

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

