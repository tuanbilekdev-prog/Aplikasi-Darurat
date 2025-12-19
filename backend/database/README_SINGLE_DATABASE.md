# ARSITEKTUR DATABASE TUNGGAL - EMERGENCY_SYSTEM

## 📋 RINGKASAN PERUBAHAN

Sistem telah diubah dari **multi-database** (admin_db + user_db) menjadi **single database** (`emergency_system`).

### ✅ SEBELUM (Multi-Database)
- `admin_db` → Tabel: instansi, alamat_instansi, admin, log_admin
- `user_db` → Tabel: users, reports, report_media
- **Masalah**: Kompleksitas koneksi, maintenance sulit, tidak scalable

### ✅ SESUDAH (Single Database)
- `emergency_system` → Semua tabel dalam satu database
- **Keuntungan**: Sederhana, mudah di-maintain, performa lebih baik

---

## 🗂️ STRUKTUR DATABASE

### Database: `emergency_system`

#### Tabel Admin:
1. **instansi** - Data instansi/organisasi
2. **alamat_instansi** - Alamat instansi (1:1 dengan instansi)
3. **admin** - Data admin yang terhubung ke instansi
4. **log_admin** - Log aktivitas admin (opsional)

#### Tabel User:
1. **users** - Data pengguna (masyarakat)
2. **reports** - Laporan darurat dari pengguna
3. **report_media** - Media/foto yang dilampirkan pada laporan

---

## 🔗 RELASI FOREIGN KEY

### Relasi Admin:
- `admin.instansi_id` → `instansi.id` (ON DELETE RESTRICT)
- `alamat_instansi.instansi_id` → `instansi.id` (ON DELETE CASCADE)
- `log_admin.admin_id` → `admin.id` (ON DELETE SET NULL)

### Relasi User:
- `reports.user_id` → `users.id` (ON DELETE CASCADE)
- `report_media.report_id` → `reports.id` (ON DELETE CASCADE)

### ⚠️ PENTING:
- **ADMIN tidak memiliki foreign key ke users**
- **USER tidak memiliki foreign key ke admin**
- Admin dan User **TERPISAH** dalam tabel berbeda

---

## 📝 URUTAN EKSEKUSI SQL

### 1. Hapus Database Lama
```sql
-- File: 00_single_database.sql (STEP 1)
DROP DATABASE IF EXISTS project_one_db;
DROP DATABASE IF EXISTS admin_db;
DROP DATABASE IF EXISTS user_db;
```

### 2. Buat Database Baru
```sql
-- File: 00_single_database.sql (STEP 2)
CREATE DATABASE IF NOT EXISTS emergency_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### 3. Buat Semua Tabel
```sql
-- File: 00_single_database.sql (STEP 3 & 4)
USE emergency_system;

-- Tabel Admin
CREATE TABLE instansi (...);
CREATE TABLE alamat_instansi (...);
CREATE TABLE admin (...);
CREATE TABLE log_admin (...);

-- Tabel User
CREATE TABLE users (...);
CREATE TABLE reports (...);
CREATE TABLE report_media (...);
```

### 4. Insert Sample Data (Opsional)
```sql
-- File: 05_insert_sample_data.sql
USE emergency_system;

-- Insert instansi, admin, users, reports
INSERT IGNORE INTO instansi (...);
INSERT IGNORE INTO admin (...);
INSERT IGNORE INTO users (...);
INSERT INTO reports (...);
```

---

## 🚀 CARA MENJALANKAN

### Via phpMyAdmin:
1. Buka phpMyAdmin
2. Pilih tab "SQL"
3. Copy-paste isi file `00_single_database.sql`
4. Klik "Go" untuk menjalankan
5. (Opsional) Jalankan `05_insert_sample_data.sql` untuk data sample

### Via Command Line:
```bash
mysql -u root -p < backend/database/00_single_database.sql
mysql -u root -p < backend/database/05_insert_sample_data.sql
```

---

## ⚙️ PERUBAHAN KODE PHP

### Connection:
```php
// SEBELUM (Multi-Database)
$admin_db = getAdminDB();
$user_db = getDB();

// SESUDAH (Single Database)
$db = getDB(); // Semua query menggunakan database yang sama
```

### Query:
```php
// Query admin
$stmt = $db->prepare("SELECT * FROM admin WHERE ...");

// Query user
$stmt = $db->prepare("SELECT * FROM users WHERE ...");

// Query reports
$stmt = $db->prepare("SELECT * FROM reports WHERE ...");
```

---

## 🔒 KEAMANAN

1. **Prepared Statements**: Semua query menggunakan prepared statements
2. **Input Sanitization**: Semua input di-sanitize sebelum digunakan
3. **Session Validation**: Validasi session di setiap halaman
4. **Password Hashing**: Menggunakan `password_hash()` dengan bcrypt
5. **Foreign Key Constraints**: Data integrity terjaga dengan foreign key

---

## ✅ VERIFIKASI

Setelah menjalankan SQL, verifikasi dengan query berikut:

```sql
-- Cek semua tabel
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'emergency_system' 
ORDER BY TABLE_NAME;

-- Cek foreign keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'emergency_system'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

---

## 📌 CATATAN PENTING

1. **Backup Data**: Selalu backup data sebelum menjalankan script
2. **Password Default**: Ganti password default setelah testing
3. **Production**: Jangan gunakan data sample di production
4. **Index**: Semua tabel sudah memiliki index yang optimal
5. **Engine**: Semua tabel menggunakan InnoDB untuk support foreign key

---

## 🎯 KESIMPULAN

Arsitektur single database lebih sederhana, mudah di-maintain, dan performa lebih baik dibanding multi-database. Admin dan User tetap terpisah dalam tabel berbeda, sehingga keamanan dan integritas data tetap terjaga.

---

**Dibuat oleh**: Software Architect & Database Engineer  
**Tanggal**: 2024  
**Versi**: 2.0 (Single Database Architecture)

