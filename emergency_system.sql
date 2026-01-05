-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 05, 2026 at 11:34 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emergency_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `instansi_id` int(11) NOT NULL COMMENT 'ID instansi tempat admin bekerja',
  `username` varchar(50) NOT NULL COMMENT 'Username untuk login',
  `email` varchar(100) NOT NULL COMMENT 'Email admin',
  `password` varchar(255) NOT NULL COMMENT 'Password ter-hash',
  `fullname` varchar(200) NOT NULL COMMENT 'Nama lengkap admin',
  `role` enum('super_admin','admin','operator') DEFAULT 'admin' COMMENT 'Role admin',
  `status` enum('active','inactive','suspended') DEFAULT 'active' COMMENT 'Status akun admin',
  `last_login` datetime DEFAULT NULL COMMENT 'Waktu login terakhir',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data admin yang terhubung ke instansi';

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `instansi_id`, `username`, `email`, `password`, `fullname`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin_dpk', 'admin.dpk@jakarta.go.id', '$2y$10$UB5Eqfn/sN/0B418toohnOf4K47nEioVrqp0KCVGwCcKhAn1mcngW', 'Admin DPK Jakarta', 'admin', 'active', '2026-01-05 17:16:06', '2025-12-19 13:39:10', '2026-01-05 17:16:06'),
(2, 2, 'admin_rsud', 'admin.rsud@jakarta.go.id', '$2y$10$UB5Eqfn/sN/0B418toohnOf4K47nEioVrqp0KCVGwCcKhAn1mcngW', 'Admin RSUD', 'admin', 'active', NULL, '2025-12-19 13:39:10', '2025-12-19 13:46:05'),
(3, 3, 'admin_polres', 'admin.polres@jakarta.go.id', '$2y$10$UB5Eqfn/sN/0B418toohnOf4K47nEioVrqp0KCVGwCcKhAn1mcngW', 'Admin Polres Jaksel', 'admin', 'active', NULL, '2025-12-19 13:39:10', '2025-12-19 13:46:05');

-- --------------------------------------------------------

--
-- Table structure for table `alamat_instansi`
--

CREATE TABLE `alamat_instansi` (
  `id` int(11) NOT NULL,
  `instansi_id` int(11) NOT NULL COMMENT 'ID instansi (1:1 relationship)',
  `alamat_lengkap` text NOT NULL COMMENT 'Alamat lengkap',
  `kelurahan` varchar(100) DEFAULT NULL COMMENT 'Kelurahan',
  `kecamatan` varchar(100) DEFAULT NULL COMMENT 'Kecamatan',
  `kota` varchar(100) NOT NULL COMMENT 'Kota/Kabupaten',
  `provinsi` varchar(100) NOT NULL COMMENT 'Provinsi',
  `kode_pos` varchar(10) DEFAULT NULL COMMENT 'Kode pos',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Koordinat latitude instansi',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Koordinat longitude instansi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel alamat instansi (1:1 dengan instansi)';

--
-- Dumping data for table `alamat_instansi`
--

INSERT INTO `alamat_instansi` (`id`, `instansi_id`, `alamat_lengkap`, `kelurahan`, `kecamatan`, `kota`, `provinsi`, `kode_pos`, `created_at`, `updated_at`, `latitude`, `longitude`) VALUES
(1, 1, 'Jl. Jend. Sudirman No. 1', 'Kebon Melati', 'Tanah Abang', 'Jakarta Pusat', 'DKI Jakarta', '10220', '2025-12-19 13:39:10', '2025-12-19 13:39:10', NULL, NULL),
(2, 2, 'Jl. Dr. Sutomo No. 8', 'Pasar Baru', 'Sawah Besar', 'Jakarta Pusat', 'DKI Jakarta', '10710', '2025-12-19 13:39:10', '2025-12-19 13:39:10', NULL, NULL),
(3, 3, 'Jl. Wijaya II No. 1', 'Petogogan', 'Kebayoran Baru', 'Jakarta Selatan', 'DKI Jakarta', '12170', '2025-12-19 13:39:10', '2025-12-19 13:39:10', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `instansi`
--

CREATE TABLE `instansi` (
  `id` int(11) NOT NULL,
  `nama` varchar(200) NOT NULL COMMENT 'Nama instansi/organisasi',
  `kode` varchar(50) NOT NULL COMMENT 'Kode unik instansi',
  `jenis` enum('pemerintah','swasta','nirlaba','lainnya') DEFAULT 'pemerintah' COMMENT 'Jenis instansi',
  `status` enum('active','inactive','suspended') DEFAULT 'active' COMMENT 'Status instansi',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu pembuatan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data instansi/organisasi';

--
-- Dumping data for table `instansi`
--

INSERT INTO `instansi` (`id`, `nama`, `kode`, `jenis`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Dinas Pemadam Kebakaran Kota Jakarta', 'DPK-JKT-001', 'pemerintah', 'active', '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(2, 'Rumah Sakit Umum Daerah', 'RSUD-001', 'pemerintah', 'active', '2025-12-19 13:39:10', '2025-12-19 13:39:10'),
(3, 'Polisi Resor Jakarta Selatan', 'POLRES-JAKSEL', 'pemerintah', 'active', '2025-12-19 13:39:10', '2025-12-19 13:39:10');

-- --------------------------------------------------------

--
-- Table structure for table `log_admin`
--

CREATE TABLE `log_admin` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL COMMENT 'ID admin yang melakukan aksi (NULL jika admin sudah dihapus)',
  `action` varchar(100) NOT NULL COMMENT 'Jenis aksi',
  `description` text DEFAULT NULL COMMENT 'Deskripsi detail aksi',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address admin',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'User agent browser',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu aksi dilakukan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log aktivitas admin untuk audit trail (opsional)';

--
-- Dumping data for table `log_admin`
--

INSERT INTO `log_admin` (`id`, `admin_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'update_report', 'Update laporan #5: Status diubah dari \'pending\' ke \'processing\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 18:05:16'),
(2, 1, 'update_report', 'Update laporan #5: Status diubah dari \'processing\' ke \'dispatched\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 18:06:43'),
(3, 1, 'update_report', 'Update laporan #6: Status diubah dari \'pending\' ke \'dispatched\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 18:20:10'),
(4, 1, 'update_report', 'Update laporan #7: Status diubah dari \'pending\' ke \'completed\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-04 19:14:29'),
(5, 1, 'update_report', 'Update laporan #1: Status diubah dari \'pending\' ke \'completed\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-04 20:01:47'),
(6, 1, 'update_report', 'Update laporan #16: Status diubah dari \'pending\' ke \'dispatched\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-04 22:49:14'),
(7, 1, 'update_report', 'Update laporan #16: Status diubah dari \'dispatched\' ke \'dispatched\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-04 22:49:19'),
(8, 1, 'update_report', 'Update laporan #3: Status diubah dari \'processing\' ke \'completed\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-04 23:10:19'),
(9, 1, 'update_report', 'Update laporan #3: Status diubah dari \'completed\' ke \'dispatched\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-04 23:10:36'),
(10, 1, 'update_report', 'Update laporan #2: Status diubah dari \'pending\' ke \'pending\'', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-04 23:42:05');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel laporan darurat dari pengguna';

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `title`, `category`, `description`, `location`, `latitude`, `longitude`, `urgent`, `status`, `admin_notes`, `dispatched_to`, `dispatched_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Peperangan di map Bermuda', 'lainnya', 'butuh bantuan sekarang', 'Jalan Seturan II, Seturan, Catur Tunggal, Depok, Sleman, Daerah Istimewa Yogyakarta, Jawa, 55821, Indonesia', -7.76821210, 110.40833410, 1, 'completed', '', NULL, NULL, '2026-01-04 20:01:47', '2026-01-04 19:39:07', '2026-01-04 20:01:47'),
(2, 1, 'Kebakaran Rumah Tinggal di Jl. Sudirman', 'kebakaran', 'Terjadi kebakaran di rumah 2 lantai, api sudah mulai membesar. Diperlukan bantuan pemadam kebakaran segera.', 'Jl. Sudirman No. 45, Jakarta Pusat', -6.20880000, 106.84560000, 1, 'pending', 'Telah menerima laporan kebakaran di rumah tinggal di Jl. Sudirman No. 45, Jakarta Pusat. Saat ini, status masih menunggu. Langkah penanganan yang akan dilakukan adalah segera menghubungi dan mengirim bantuan dari petugas pemadam kebakaran ke lokasi untuk memadamkan api. Harap tetap tenang dan menjauhi area kejadian. Mohon untuk tidak mencoba memadamkan api sendiri. Akan terus dipantau perkembangan situasi. Terima kasih atas laporannya.', 'Pemadam', NULL, NULL, '2026-01-04 19:40:17', '2026-01-04 23:42:05'),
(3, 1, 'Kebakaran Gedung Perkantoran', 'kebakaran', 'Asap tebal keluar dari lantai 3 gedung perkantoran. Evakuasi sedang berlangsung.', 'Jl. Thamrin No. 10, Jakarta Pusat', -6.19440000, 106.82290000, 1, 'dispatched', '', NULL, '2026-01-04 23:10:36', '2026-01-04 23:10:19', '2026-01-04 17:40:17', '2026-01-04 23:10:36'),
(4, 2, 'Kecelakaan Lalu Lintas di Perempatan', 'kecelakaan', 'Tabrakan antara mobil dan motor di perempatan. Korban membutuhkan bantuan medis.', 'Jl. Gatot Subroto, Jakarta Selatan', -6.22970000, 106.79890000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 18:40:17', '2026-01-04 19:40:17'),
(5, 2, 'Kecelakaan Mobil Tabrakan dengan Truk', 'kecelakaan', 'Kecelakaan serius di jalan tol. Beberapa korban luka-luka membutuhkan ambulans.', 'Jl. Tol Jagorawi Km 10', -6.25000000, 106.90000000, 1, 'dispatched', NULL, NULL, NULL, NULL, '2026-01-04 16:40:17', '2026-01-04 19:40:17'),
(6, 2, 'Kecelakaan Sepeda Motor', 'kecelakaan', 'Kecelakaan tunggal sepeda motor. Pengendara terjatuh dan mengalami luka di tangan.', 'Jl. Kebayoran Baru, Jakarta Selatan', -6.24330000, 106.79950000, 0, 'completed', NULL, NULL, NULL, NULL, '2026-01-04 14:40:17', '2026-01-04 19:40:17'),
(7, 3, 'Kebutuhan Ambulans Medis', 'medis', 'Warga membutuhkan ambulans untuk evakuasi korban sakit. Kondisi korban cukup parah.', 'Jl. Senopati, Jakarta Selatan', -6.24440000, 106.80050000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 19:10:17', '2026-01-04 19:40:17'),
(8, 1, 'Darurat Medis di Rumah', 'medis', 'Pasien pingsan dan membutuhkan bantuan medis segera. Keluarga meminta ambulans.', 'Jl. Cipete Raya, Jakarta Selatan', -6.26000000, 106.81000000, 1, 'processing', NULL, NULL, NULL, NULL, '2026-01-04 18:55:17', '2026-01-04 19:40:17'),
(9, 2, 'Kecelakaan dengan Korban Luka', 'medis', 'Ada korban luka akibat kecelakaan. Diperlukan pertolongan medis segera.', 'Jl. Rasuna Said, Jakarta Selatan', -6.23800000, 106.83000000, 1, 'dispatched', NULL, NULL, NULL, NULL, '2026-01-04 17:40:17', '2026-01-04 19:40:17'),
(10, 3, 'Pencurian Kendaraan Bermotor', 'kejahatan', 'Motor hilang dicuri di depan rumah. Sudah dilaporkan ke polisi setempat.', 'Jl. Kemang Raya, Jakarta Selatan', -6.25800000, 106.80800000, 0, 'pending', NULL, NULL, NULL, NULL, '2026-01-03 19:40:17', '2026-01-04 19:40:17'),
(11, 1, 'Perampokan di Jalan Raya', 'kejahatan', 'Terjadi perampokan di jalan raya. Korban kehilangan barang berharga. Diperlukan patroli polisi.', 'Jl. Kemang Timur, Jakarta Selatan', -6.25700000, 106.80900000, 1, 'processing', NULL, NULL, NULL, NULL, '2026-01-04 15:40:17', '2026-01-04 19:40:17'),
(12, 2, 'Banjir di Permukiman', 'bencana', 'Kawasan permukiman terendam banjir setinggi 50cm. Warga membutuhkan bantuan evakuasi.', 'Jl. Cilandak, Jakarta Selatan', -6.27000000, 106.78000000, 1, 'pending', NULL, NULL, NULL, NULL, '2026-01-02 19:40:17', '2026-01-04 19:40:17'),
(13, 3, 'Pohon Tumbang Menutup Jalan', 'bencana', 'Pohon besar tumbang akibat hujan deras dan menutup jalan. Lalu lintas terhambat.', 'Jl. Fatmawati, Jakarta Selatan', -6.24500000, 106.79500000, 0, 'dispatched', NULL, NULL, NULL, NULL, '2026-01-04 13:40:17', '2026-01-04 19:40:17'),
(14, 1, 'Gangguan Listrik di Perumahan', 'lainnya', 'Listrik padam di seluruh perumahan. Warga membutuhkan bantuan PLN untuk perbaikan.', 'Jl. Lebak Bulus, Jakarta Selatan', -6.29000000, 106.77000000, 0, 'pending', NULL, NULL, NULL, NULL, '2026-01-04 16:40:17', '2026-01-04 19:40:17'),
(15, 2, 'Kerusakan Jalan Berlubang', 'lainnya', 'Jalan berlubang besar di tengah jalan. Berbahaya untuk pengendara. Diperlukan perbaikan.', 'Jl. TB Simatupang, Jakarta Selatan', -6.30000000, 106.80000000, 0, 'completed', NULL, NULL, NULL, NULL, '2026-01-03 19:40:17', '2026-01-04 19:40:17'),
(16, 1, 'Kos Zikri', 'kejahatan', 'jangan datang tak diundang', 'Umbulmartani, Ngemplak, Sleman, Daerah Istimewa Yogyakarta, Jawa, 55786, Indonesia', -7.68163040, 110.42318240, 1, 'dispatched', 'kawan mabar lagi otewe', 'kawan mabar', '2026-01-04 22:49:14', NULL, '2026-01-04 22:29:04', '2026-01-04 22:49:19');

-- --------------------------------------------------------

--
-- Table structure for table `report_media`
--

CREATE TABLE `report_media` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL COMMENT 'ID laporan yang memiliki media ini',
  `file_path` varchar(500) NOT NULL COMMENT 'Path file di server',
  `file_type` varchar(50) DEFAULT NULL COMMENT 'Tipe file',
  `file_size` int(11) DEFAULT NULL COMMENT 'Ukuran file dalam bytes',
  `file_name` varchar(255) DEFAULT NULL COMMENT 'Nama file asli',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Waktu upload'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel media/foto yang dilampirkan pada laporan (opsional)';

--
-- Dumping data for table `report_media`
--

INSERT INTO `report_media` (`id`, `report_id`, `file_path`, `file_type`, `file_size`, `file_name`, `created_at`) VALUES
(1, 1, 'uploads/reports/1767530347_695a5f6b65ebd_07b78102.png', 'image/png', 873830, 'Screenshot 2026-01-04 193144.png', '2026-01-04 19:39:07'),
(2, 16, 'uploads/reports/1767540544_695a874013991_bf9fc2ca.png', 'image/png', 570703, 'Screenshot 2025-12-29 011705.png', '2026-01-04 22:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Waktu update terakhir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel data pengguna (masyarakat)';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `fullname`, `phone`, `status`, `google_id`, `remember_token`, `remember_expiry`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'john_doe', 'john.doe@example.com', '$2y$10$vxBJSTmULtP4uNBKXTWfme.4jMEGwn2LWkrtTZqRGdauXkbxxWG16', 'John Doe', '081234567890', 'active', NULL, NULL, NULL, '2026-01-05 10:38:13', '2025-12-19 13:39:10', '2026-01-05 10:38:13'),
(2, 'jane_smith', 'jane.smith@example.com', '$2y$10$vxBJSTmULtP4uNBKXTWfme.4jMEGwn2LWkrtTZqRGdauXkbxxWG16', 'Jane Smith', '081234567891', 'active', NULL, NULL, NULL, NULL, '2025-12-19 13:39:10', '2025-12-19 13:46:05'),
(3, 'budi_santoso', 'budi.santoso@example.com', '$2y$10$vxBJSTmULtP4uNBKXTWfme.4jMEGwn2LWkrtTZqRGdauXkbxxWG16', 'Budi Santoso', '081234567892', 'active', NULL, NULL, NULL, NULL, '2025-12-19 13:39:10', '2025-12-19 13:46:05'),
(4, 'Tesuser', 'Tesuser@gmail.com', '$2y$10$y9iwRUPI8vp5/DrtMntUuurpOXDZdUHNrZ3P7TbdinAjOKNlsGlaK', 'Tesuser', NULL, 'active', NULL, NULL, NULL, '2025-12-20 18:13:09', '2025-12-20 18:13:00', '2025-12-20 18:13:09'),
(5, 'azzikriassidqisamani', '24523080@students.uii.ac.id', '$2y$10$Ldy45oQc7S2DtjWtUuvctOXuvhYzhhcgeK8e7cgB5fsczG.IBvIxS', 'AZZIKRI ASSIDQI SAMANI -', NULL, 'active', '101393614752277239480', NULL, NULL, '2026-01-05 17:14:36', '2026-01-04 17:48:46', '2026-01-05 17:14:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_instansi_id` (`instansi_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `alamat_instansi`
--
ALTER TABLE `alamat_instansi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `instansi_id` (`instansi_id`),
  ADD KEY `idx_instansi_id` (`instansi_id`),
  ADD KEY `idx_kota` (`kota`),
  ADD KEY `idx_provinsi` (`provinsi`),
  ADD KEY `idx_coordinates` (`latitude`,`longitude`);

--
-- Indexes for table `instansi`
--
ALTER TABLE `instansi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `idx_kode` (`kode`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_urgent` (`urgent`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_location` (`latitude`,`longitude`);

--
-- Indexes for table `report_media`
--
ALTER TABLE `report_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_id` (`report_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `alamat_instansi`
--
ALTER TABLE `alamat_instansi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `instansi`
--
ALTER TABLE `instansi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `log_admin`
--
ALTER TABLE `log_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `report_media`
--
ALTER TABLE `report_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`instansi_id`) REFERENCES `instansi` (`id`);

--
-- Constraints for table `alamat_instansi`
--
ALTER TABLE `alamat_instansi`
  ADD CONSTRAINT `alamat_instansi_ibfk_1` FOREIGN KEY (`instansi_id`) REFERENCES `instansi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD CONSTRAINT `log_admin_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_media`
--
ALTER TABLE `report_media`
  ADD CONSTRAINT `report_media_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
