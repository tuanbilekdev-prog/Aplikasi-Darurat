-- ============================================
-- PROJECT ONE - SAMPLE DATA
-- Script untuk memasukkan data contoh (untuk testing)
-- ============================================
-- 
-- PERINGATAN: 
-- - Script ini hanya untuk development/testing
-- - JANGAN gunakan di production tanpa modifikasi
-- - Password default harus diganti setelah testing
-- 
-- DATABASE: emergency_system (single database)
-- ============================================

USE emergency_system;

-- ============================================
-- SAMPLE DATA: INSTANSI & ADMIN
-- ============================================

-- Insert sample instansi (gunakan INSERT IGNORE untuk menghindari duplicate)
INSERT IGNORE INTO instansi (id, nama, kode, jenis, status) VALUES
(1, 'Dinas Pemadam Kebakaran Kota Jakarta', 'DPK-JKT-001', 'pemerintah', 'active'),
(2, 'Rumah Sakit Umum Daerah', 'RSUD-001', 'pemerintah', 'active'),
(3, 'Polisi Resor Jakarta Selatan', 'POLRES-JAKSEL', 'pemerintah', 'active');

-- Insert sample alamat instansi (gunakan INSERT IGNORE untuk menghindari duplicate)
INSERT IGNORE INTO alamat_instansi (instansi_id, alamat_lengkap, kelurahan, kecamatan, kota, provinsi, kode_pos) VALUES
(1, 'Jl. Jend. Sudirman No. 1', 'Kebon Melati', 'Tanah Abang', 'Jakarta Pusat', 'DKI Jakarta', '10220'),
(2, 'Jl. Dr. Sutomo No. 8', 'Pasar Baru', 'Sawah Besar', 'Jakarta Pusat', 'DKI Jakarta', '10710'),
(3, 'Jl. Wijaya II No. 1', 'Petogogan', 'Kebayoran Baru', 'Jakarta Selatan', 'DKI Jakarta', '12170');

-- Insert sample admin (gunakan INSERT IGNORE untuk menghindari duplicate)
-- PENTING: Ganti password hash dengan yang di-generate dari PHP
-- Gunakan: password_hash('admin123', PASSWORD_DEFAULT)
-- Contoh hash (untuk password 'admin123'):
-- $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT IGNORE INTO admin (instansi_id, username, email, password, fullname, role, status) VALUES
(1, 'admin_dpk', 'admin.dpk@jakarta.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin DPK Jakarta', 'admin', 'active'),
(2, 'admin_rsud', 'admin.rsud@jakarta.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin RSUD', 'admin', 'active'),
(3, 'admin_polres', 'admin.polres@jakarta.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Polres Jaksel', 'admin', 'active');

-- ============================================
-- SAMPLE DATA: USER & REPORTS
-- ============================================

-- Insert sample users (gunakan INSERT IGNORE untuk menghindari duplicate)
-- PENTING: Ganti password hash dengan yang di-generate dari PHP
-- Gunakan: password_hash('user123', PASSWORD_DEFAULT)
INSERT IGNORE INTO users (id, username, email, password, fullname, phone, status) VALUES
(1, 'john_doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '081234567890', 'active'),
(2, 'jane_smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '081234567891', 'active'),
(3, 'budi_santoso', 'budi.santoso@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', '081234567892', 'active');

-- Insert sample reports (bisa di-insert berulang, tidak ada unique constraint)
INSERT INTO reports (user_id, title, category, description, location, latitude, longitude, urgent, status) VALUES
(1, 'Kebakaran Rumah di Jl. Sudirman', 'kebakaran', 'Terjadi kebakaran di rumah 2 lantai, api sudah mulai membesar', 'Jl. Sudirman No. 45, Jakarta Pusat', -6.2088, 106.8456, 1, 'pending'),
(2, 'Kecelakaan Lalu Lintas', 'kecelakaan', 'Tabrakan antara mobil dan motor di perempatan', 'Jl. Thamrin, Jakarta Pusat', -6.1944, 106.8229, 1, 'processing'),
(3, 'Kebutuhan Ambulans Medis', 'medis', 'Warga membutuhkan ambulans untuk evakuasi korban sakit', 'Jl. Kebayoran Baru, Jakarta Selatan', -6.2433, 106.7995, 1, 'pending');

-- ============================================
-- CATATAN PENTING
-- ============================================
-- 
-- 1. Password default untuk semua akun: 'admin123' atau 'user123'
--    HARUS diganti setelah testing!
-- 
-- 2. Untuk generate password hash baru, gunakan PHP:
--    <?php
--    echo password_hash('password_anda', PASSWORD_DEFAULT);
--    ?>
-- 
-- 3. Data sample ini hanya untuk testing
--    Hapus atau modifikasi sebelum production
-- 
-- 4. Database: emergency_system (single database)
--    Semua tabel (admin & user) berada dalam satu database
-- 
-- ============================================
