-- ============================================
-- PROJECT ONE - CLEAR ALL DATA
-- Script untuk menghapus semua data dari database
-- 
-- PERINGATAN: Script ini akan menghapus SEMUA data dari semua tabel!
-- Gunakan dengan hati-hati dan pastikan sudah backup data jika diperlukan.
-- ============================================

-- Nonaktifkan foreign key checks sementara untuk memudahkan truncate
SET FOREIGN_KEY_CHECKS = 0;

-- Hapus semua data dari tabel (urutan tidak penting karena FK checks dinonaktifkan)
TRUNCATE TABLE report_media;
TRUNCATE TABLE reports;
TRUNCATE TABLE admin;
TRUNCATE TABLE users;
TRUNCATE TABLE alamat_instansi;
TRUNCATE TABLE instansi;

-- Jika ada tabel log_admin, hapus juga
TRUNCATE TABLE log_admin;

-- Aktifkan kembali foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verifikasi: Cek apakah semua tabel kosong
SELECT 'Tabel report_media' AS tabel, COUNT(*) AS jumlah FROM report_media
UNION ALL
SELECT 'Tabel reports', COUNT(*) FROM reports
UNION ALL
SELECT 'Tabel admin', COUNT(*) FROM admin
UNION ALL
SELECT 'Tabel users', COUNT(*) FROM users
UNION ALL
SELECT 'Tabel alamat_instansi', COUNT(*) FROM alamat_instansi
UNION ALL
SELECT 'Tabel instansi', COUNT(*) FROM instansi;

-- ============================================
-- SELESAI
-- ============================================
-- Setelah menjalankan script ini:
-- 1. Semua data akan terhapus
-- 2. AUTO_INCREMENT akan direset ke 1
-- 3. Struktur tabel tetap utuh
-- 4. Untuk memasukkan data lagi, jalankan 05_insert_sample_data.sql
-- ============================================

