-- ============================================
-- PROJECT ONE - VERIFY STATISTICS
-- Script untuk memverifikasi perhitungan statistik dashboard
-- ============================================

USE emergency_system;

-- 1. Total Laporan (harus sama dengan jumlah semua baris)
SELECT '1. Total Laporan' AS keterangan, COUNT(*) AS jumlah FROM reports;

-- 2. Laporan Hari Ini (berdasarkan CURDATE)
SELECT '2. Laporan Hari Ini' AS keterangan, COUNT(*) AS jumlah 
FROM reports 
WHERE DATE(created_at) = CURDATE();

-- 3. Laporan Darurat (urgent = 1, belum selesai/dibatalkan)
SELECT '3. Laporan Darurat (belum selesai)' AS keterangan, COUNT(*) AS jumlah 
FROM reports 
WHERE urgent = 1 AND status != 'completed' AND status != 'cancelled';

-- 4. Laporan Darurat SEMUA (tanpa filter status)
SELECT '3b. Laporan Darurat (SEMUA)' AS keterangan, COUNT(*) AS jumlah 
FROM reports 
WHERE urgent = 1;

-- 5. Breakdown Status
SELECT '5. Breakdown Status' AS keterangan, status, COUNT(*) AS jumlah 
FROM reports 
GROUP BY status
ORDER BY status;

-- 6. Detail Status (pending, processing, dispatched, completed)
SELECT 
    '6. Detail Status' AS keterangan,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
    SUM(CASE WHEN status = 'dispatched' THEN 1 ELSE 0 END) AS dispatched,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
    COUNT(*) AS total
FROM reports;

-- 7. Laporan Darurat per Status
SELECT 
    '7. Laporan Darurat per Status' AS keterangan,
    status,
    COUNT(*) AS jumlah
FROM reports 
WHERE urgent = 1
GROUP BY status
ORDER BY status;

-- 8. Breakdown Kategori
SELECT '8. Breakdown Kategori' AS keterangan, category, COUNT(*) AS jumlah 
FROM reports 
GROUP BY category
ORDER BY category;

-- 9. Verifikasi: Jumlah Status harus = Total (kecuali cancelled)
SELECT 
    '9. Verifikasi Jumlah Status' AS keterangan,
    (SELECT COUNT(*) FROM reports WHERE status = 'pending') +
    (SELECT COUNT(*) FROM reports WHERE status = 'processing') +
    (SELECT COUNT(*) FROM reports WHERE status = 'dispatched') +
    (SELECT COUNT(*) FROM reports WHERE status = 'completed') +
    (SELECT COUNT(*) FROM reports WHERE status = 'cancelled') AS total_status,
    (SELECT COUNT(*) FROM reports) AS total_all,
    CASE 
        WHEN (SELECT COUNT(*) FROM reports WHERE status = 'pending') +
             (SELECT COUNT(*) FROM reports WHERE status = 'processing') +
             (SELECT COUNT(*) FROM reports WHERE status = 'dispatched') +
             (SELECT COUNT(*) FROM reports WHERE status = 'completed') +
             (SELECT COUNT(*) FROM reports WHERE status = 'cancelled') = 
             (SELECT COUNT(*) FROM reports)
        THEN 'SESUAI'
        ELSE 'TIDAK SESUAI'
    END AS status_verifikasi;

-- 10. Laporan dengan urgent = 1 per status
SELECT 
    '10. Laporan Darurat per Status (Detail)' AS keterangan,
    status,
    urgent,
    COUNT(*) AS jumlah
FROM reports 
WHERE urgent = 1
GROUP BY status, urgent
ORDER BY status;

-- ============================================
-- CATATAN
-- ============================================
-- Query #3 adalah yang digunakan di dashboard untuk "Laporan Darurat"
-- Query ini hanya menghitung laporan darurat yang BELUM selesai/dibatalkan
-- 
-- Jika ada laporan darurat yang sudah selesai (completed), tidak akan terhitung
-- Ini adalah logika yang benar karena laporan darurat yang sudah selesai
-- tidak perlu ditampilkan sebagai prioritas
-- ============================================

