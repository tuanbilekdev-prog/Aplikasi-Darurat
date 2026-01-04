-- ============================================
-- PROJECT ONE - RESET DATABASE (DROP & RECREATE)
-- Script untuk menghapus database dan membuat ulang dari awal
-- 
-- PERINGATAN EKSTREM: Script ini akan MENGHAPUS database dan membuat ulang!
-- SEMUA data akan hilang PERMANEN!
-- Gunakan hanya jika ingin benar-benar mulai dari nol.
-- ============================================

-- Nonaktifkan foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Hapus database jika ada
DROP DATABASE IF EXISTS emergency_system;

-- Buat database baru
CREATE DATABASE IF NOT EXISTS emergency_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Gunakan database
USE emergency_system;

-- Aktifkan kembali foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- CATATAN
-- ============================================
-- Setelah menjalankan script ini:
-- 1. Database akan kosong (belum ada tabel)
-- 2. Jalankan 00_single_database.sql untuk membuat struktur tabel
-- 3. Jalankan 05_insert_sample_data.sql untuk memasukkan data sample
-- ============================================

