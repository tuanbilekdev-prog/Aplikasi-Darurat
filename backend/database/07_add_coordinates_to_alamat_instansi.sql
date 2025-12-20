-- ============================================
-- MIGRATION: Tambah Kolom Koordinat ke alamat_instansi
-- ============================================
-- Script ini menambahkan kolom latitude dan longitude ke tabel alamat_instansi
-- untuk mendukung cache database berdasarkan radius lokasi
-- 
-- Tujuan: Menghemat credit Google Places API dengan cache berdasarkan koordinat
-- ============================================

USE emergency_system;

-- Tambah kolom latitude dan longitude jika belum ada
ALTER TABLE alamat_instansi 
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) NULL COMMENT 'Koordinat latitude instansi',
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) NULL COMMENT 'Koordinat longitude instansi';

-- Tambah index untuk performa query berdasarkan koordinat
ALTER TABLE alamat_instansi 
ADD INDEX IF NOT EXISTS idx_coordinates (latitude, longitude);

-- Catatan: 
-- - Kolom nullable karena data lama mungkin belum punya koordinat
-- - Index akan mempercepat query radius menggunakan formula Haversine
-- - Koordinat akan diisi otomatis saat admin mencari instansi via Places API

