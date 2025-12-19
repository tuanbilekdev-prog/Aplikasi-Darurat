-- ============================================
-- PROJECT ONE - SINGLE DATABASE ARCHITECTURE
-- Script untuk membuat database tunggal emergency_system
-- ============================================
-- 
-- PERINGATAN PENTING:
-- ⚠️  Script ini akan MENGHAPUS SEMUA DATABASE LAMA
-- ⚠️  Pastikan Anda sudah backup data penting
-- ⚠️  Jangan jalankan di production tanpa persiapan
-- 
-- ARSITEKTUR:
-- - SATU DATABASE: emergency_system
-- - Tabel ADMIN: instansi, alamat_instansi, admin, log_admin
-- - Tabel USER: users, reports, report_media
-- - ADMIN dan USER TERPISAH dalam tabel berbeda
-- 
-- CARA PENGGUNAAN:
-- 1. Backup database lama terlebih dahulu
-- 2. Jalankan script ini via command line atau phpMyAdmin
-- 3. Verifikasi hasil dengan query di akhir script
-- 
-- ============================================

-- Set SQL mode untuk kompatibilitas
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- STEP 1: HAPUS DATABASE LAMA
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS project_one_db;
DROP DATABASE IF EXISTS admin_db;
DROP DATABASE IF EXISTS user_db;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- STEP 2: BUAT DATABASE TUNGGAL
-- ============================================
CREATE DATABASE IF NOT EXISTS emergency_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE emergency_system;

-- ============================================
-- STEP 3: BUAT TABEL ADMIN
-- ============================================

-- Tabel: instansi
-- Menyimpan data instansi/organisasi (Rumah Sakit, Polisi, Pemadam, dll)
CREATE TABLE IF NOT EXISTS instansi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(200) NOT NULL COMMENT 'Nama instansi/organisasi',
    kode VARCHAR(50) UNIQUE NOT NULL COMMENT 'Kode unik instansi',
    jenis ENUM('pemerintah', 'swasta', 'nirlaba', 'lainnya') DEFAULT 'pemerintah' COMMENT 'Jenis instansi',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' COMMENT 'Status instansi',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    INDEX idx_kode (kode),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel data instansi/organisasi';

-- Tabel: alamat_instansi
-- Menyimpan alamat lengkap instansi (1:1 dengan instansi)
CREATE TABLE IF NOT EXISTS alamat_instansi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instansi_id INT NOT NULL UNIQUE COMMENT 'ID instansi (1:1 relationship)',
    alamat_lengkap TEXT NOT NULL COMMENT 'Alamat lengkap',
    kelurahan VARCHAR(100) NULL COMMENT 'Kelurahan',
    kecamatan VARCHAR(100) NULL COMMENT 'Kecamatan',
    kota VARCHAR(100) NOT NULL COMMENT 'Kota/Kabupaten',
    provinsi VARCHAR(100) NOT NULL COMMENT 'Provinsi',
    kode_pos VARCHAR(10) NULL COMMENT 'Kode pos',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    FOREIGN KEY (instansi_id) REFERENCES instansi(id) ON DELETE CASCADE,
    INDEX idx_instansi_id (instansi_id),
    INDEX idx_kota (kota),
    INDEX idx_provinsi (provinsi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel alamat instansi (1:1 dengan instansi)';

-- Tabel: admin
-- Menyimpan data admin yang terhubung ke instansi
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instansi_id INT NOT NULL COMMENT 'ID instansi tempat admin bekerja',
    username VARCHAR(50) UNIQUE NOT NULL COMMENT 'Username untuk login',
    email VARCHAR(100) UNIQUE NOT NULL COMMENT 'Email admin',
    password VARCHAR(255) NOT NULL COMMENT 'Password ter-hash',
    fullname VARCHAR(200) NOT NULL COMMENT 'Nama lengkap admin',
    role ENUM('super_admin', 'admin', 'operator') DEFAULT 'admin' COMMENT 'Role admin',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' COMMENT 'Status akun admin',
    last_login DATETIME NULL COMMENT 'Waktu login terakhir',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    FOREIGN KEY (instansi_id) REFERENCES instansi(id) ON DELETE RESTRICT,
    INDEX idx_instansi_id (instansi_id),
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel data admin yang terhubung ke instansi';

-- Tabel: log_admin (opsional)
-- Log aktivitas admin untuk audit trail
CREATE TABLE IF NOT EXISTS log_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL COMMENT 'ID admin yang melakukan aksi (NULL jika admin sudah dihapus)',
    action VARCHAR(100) NOT NULL COMMENT 'Jenis aksi',
    description TEXT NULL COMMENT 'Deskripsi detail aksi',
    ip_address VARCHAR(45) NULL COMMENT 'IP address admin',
    user_agent VARCHAR(255) NULL COMMENT 'User agent browser',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu aksi dilakukan',
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log aktivitas admin untuk audit trail (opsional)';

-- ============================================
-- STEP 4: BUAT TABEL USER
-- ============================================

-- Tabel: users
-- Menyimpan data pengguna (masyarakat) yang melaporkan kejadian
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL COMMENT 'Username untuk login',
    email VARCHAR(100) UNIQUE NOT NULL COMMENT 'Email pengguna',
    password VARCHAR(255) NOT NULL COMMENT 'Password ter-hash',
    fullname VARCHAR(200) NOT NULL COMMENT 'Nama lengkap pengguna',
    phone VARCHAR(20) NULL COMMENT 'Nomor telepon (opsional)',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' COMMENT 'Status akun pengguna',
    google_id VARCHAR(100) NULL COMMENT 'Google OAuth ID',
    remember_token VARCHAR(255) NULL COMMENT 'Token untuk remember me',
    remember_expiry INT NULL COMMENT 'Expiry timestamp untuk remember token',
    last_login DATETIME NULL COMMENT 'Waktu login terakhir',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_google_id (google_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel data pengguna (masyarakat)';

-- Tabel: reports
-- Menyimpan laporan darurat dari pengguna
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID pengguna yang membuat laporan',
    title VARCHAR(200) NOT NULL COMMENT 'Judul laporan',
    category ENUM('kecelakaan', 'kebakaran', 'medis', 'kejahatan', 'bencana', 'lainnya') NOT NULL COMMENT 'Kategori kejadian',
    description TEXT NOT NULL COMMENT 'Deskripsi lengkap kejadian',
    location VARCHAR(255) NOT NULL COMMENT 'Lokasi kejadian (alamat)',
    latitude DECIMAL(10, 8) NULL COMMENT 'Koordinat latitude (untuk GPS)',
    longitude DECIMAL(11, 8) NULL COMMENT 'Koordinat longitude (untuk GPS)',
    urgent TINYINT(1) DEFAULT 0 COMMENT 'Flag darurat (1=urgent, 0=normal)',
    status ENUM('pending', 'processing', 'dispatched', 'completed', 'cancelled') DEFAULT 'pending' COMMENT 'Status laporan',
    admin_notes TEXT NULL COMMENT 'Catatan dari admin (dari sistem dispatch)',
    dispatched_to VARCHAR(200) NULL COMMENT 'Instansi yang ditugaskan (dari sistem dispatch)',
    dispatched_at DATETIME NULL COMMENT 'Waktu dispatch ke instansi',
    completed_at DATETIME NULL COMMENT 'Waktu laporan selesai',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pembuatan',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Waktu update terakhir',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_urgent (urgent),
    INDEX idx_created_at (created_at),
    INDEX idx_location (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel laporan darurat dari pengguna';

-- Tabel: report_media (opsional)
-- Menyimpan media/foto yang dilampirkan pada laporan
CREATE TABLE IF NOT EXISTS report_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL COMMENT 'ID laporan yang memiliki media ini',
    file_path VARCHAR(500) NOT NULL COMMENT 'Path file di server',
    file_type VARCHAR(50) NULL COMMENT 'Tipe file',
    file_size INT NULL COMMENT 'Ukuran file dalam bytes',
    file_name VARCHAR(255) NULL COMMENT 'Nama file asli',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload',
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    INDEX idx_report_id (report_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel media/foto yang dilampirkan pada laporan (opsional)';

-- ============================================
-- VERIFIKASI: Cek hasil pembuatan database
-- ============================================
-- Uncomment query di bawah untuk verifikasi

-- SELECT 'emergency_system Tables:' AS info;
-- SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
-- WHERE TABLE_SCHEMA = 'emergency_system' 
-- ORDER BY TABLE_NAME;
-- 
-- SELECT 'Old databases removed:' AS info;
-- SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA 
-- WHERE SCHEMA_NAME IN ('project_one_db', 'admin_db', 'user_db');
-- -- Hasil harus kosong jika berhasil

-- ============================================
-- SELESAI
-- ============================================
-- Database emergency_system berhasil dibuat!
-- Langkah selanjutnya:
-- 1. Update konfigurasi PHP (config.php, connection.php)
-- 2. Test koneksi ke database baru
-- 3. Insert data sample jika diperlukan (05_insert_sample_data.sql)

