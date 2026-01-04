-- ============================================
-- PROJECT ONE - CLEAR REPORTS DATA ONLY
-- Script untuk menghapus data laporan saja (tidak menghapus user, admin, instansi)
-- 
-- PERINGATAN: Script ini akan menghapus SEMUA data laporan!
-- Data user, admin, dan instansi TETAP UTUH.
-- ============================================

-- Nonaktifkan foreign key checks sementara untuk memudahkan truncate
SET FOREIGN_KEY_CHECKS = 0;

-- Hapus semua data laporan
-- Urutan: report_media dulu (child), baru reports (parent)
TRUNCATE TABLE report_media;
TRUNCATE TABLE reports;

-- Aktifkan kembali foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- CATATAN: Jika error masih muncul, gunakan query DELETE di bawah ini sebagai alternatif:
-- DELETE FROM report_media;
-- DELETE FROM reports;
-- ALTER TABLE report_media AUTO_INCREMENT = 1;
-- ALTER TABLE reports AUTO_INCREMENT = 1;

-- Verifikasi: Cek apakah tabel laporan sudah kosong
SELECT 'Tabel report_media' AS tabel, COUNT(*) AS jumlah FROM report_media
UNION ALL
SELECT 'Tabel reports', COUNT(*) FROM reports;

-- ============================================
-- SELESAI
-- ============================================
-- Setelah menjalankan script ini:
-- 1. Semua data laporan akan terhapus
-- 2. Data user, admin, dan instansi TETAP UTUH
-- 3. AUTO_INCREMENT akan direset ke 1
-- 4. Struktur tabel tetap utuh
-- ============================================

