-- ============================================
-- PROJECT ONE - CLEAR REPORTS DATA (SIMPLE VERSION)
-- Script sederhana untuk menghapus data laporan saja
-- Menggunakan DELETE FROM (lebih aman dengan foreign key)
-- ============================================

-- Hapus semua data laporan (dengan foreign key constraint tetap aktif)
-- Urutan: report_media dulu (child), baru reports (parent)
DELETE FROM report_media;
DELETE FROM reports;

-- Reset AUTO_INCREMENT ke 1
ALTER TABLE report_media AUTO_INCREMENT = 1;
ALTER TABLE reports AUTO_INCREMENT = 1;

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

