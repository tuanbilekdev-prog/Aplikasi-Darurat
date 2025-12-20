# Optimasi Google Places API - Hemat Credit

## 📋 Ringkasan Optimasi

Aplikasi ini telah dioptimasi untuk **menghemat credit Google Places API** secara signifikan. Tanpa optimasi ini, aplikasi bisa menghabiskan ratusan dollar per bulan. Dengan optimasi ini, biaya bisa turun menjadi **< $10 per bulan**.

## 🚫 Fitur yang DIHAPUS

### 1. Autocomplete (DIHAPUS)
- **Sebelum:** Autocomplete dipanggil setiap kali user mengetik di input lokasi
- **Masalah:** Bisa menghabiskan ratusan credit per hari
- **Biaya:** $2.83 per 1000 requests
- **Solusi:** DIHAPUS sepenuhnya dari user form

**File yang diubah:**
- `backend/user/create_report.php` - Menghapus `libraries=places` dari Google Maps API

### 2. Request Otomatis (DIHAPUS)
- **Sebelum:** Places API dipanggil saat:
  - Halaman load
  - Input berubah
  - Map digeser/di-zoom
- **Masalah:** Request terus-menerus tanpa kontrol
- **Solusi:** Semua request otomatis DIHAPUS

## ✅ Fitur yang DITAMBAHKAN

### 1. Tombol Manual "Cari Instansi Darurat Terdekat"
- **Lokasi:** Admin Map Dashboard (`backend/admin/map_dashboard.php`)
- **Fungsi:** Places API hanya dipanggil saat admin menekan tombol ini
- **Manfaat:** Mengurangi request dari ratusan/jam menjadi hanya beberapa kali per hari

### 2. Cache Database
- **Lokasi:** `backend/admin/api_find_instansi.php`
- **Fungsi:** 
  - Sebelum memanggil Places API, sistem cek dulu data di database
  - Jika sudah ada instansi dalam radius yang sama, gunakan data dari database
  - Jika tidak ada, baru request ke Places API dan simpan hasilnya ke database
- **Manfaat:** Menghemat 80-90% credit Places API

### 3. Batasan Query
- **Type:** Hanya `hospital`, `police`, `fire_station`
- **Radius:** Maksimal 5000 meter
- **Manfaat:** Mengurangi jumlah hasil dan credit yang digunakan

## 📊 Perbandingan Biaya

### Sebelum Optimasi:
- Autocomplete: 1000 requests/hari × $2.83/1000 = **$2.83/hari** = **$85/bulan**
- Nearby Search: 500 requests/hari × $32/1000 = **$16/hari** = **$480/bulan**
- **Total: ~$565/bulan** 💸

### Setelah Optimasi:
- Autocomplete: **0 requests** (DIHAPUS) = **$0**
- Nearby Search: 10 requests/hari × $32/1000 = **$0.32/hari** = **$9.6/bulan**
- **Total: ~$10/bulan** ✅

**Penghematan: ~$555/bulan (98% lebih hemat!)** 🎉

## 🔐 Keamanan

1. **Admin-Only Access:**
   - Endpoint `api_find_instansi.php` hanya bisa diakses oleh admin yang sudah login
   - Menggunakan middleware `auth_admin.php`

2. **API Key:**
   - Tidak hardcoded di banyak file
   - Menggunakan konstanta `GOOGLE_MAPS_API_KEY` dari `config.php`
   - Semua file menggunakan konstanta yang sama

## 🗄️ Database Cache

### Tabel yang Digunakan:
- `instansi` - Menyimpan data instansi
- `alamat_instansi` - Menyimpan alamat dan koordinat instansi

### Migration Script:
- `backend/database/07_add_coordinates_to_alamat_instansi.sql`
- Menambahkan kolom `latitude` dan `longitude` ke tabel `alamat_instansi`

### Cara Kerja Cache:
1. Admin klik tombol "Cari Instansi Darurat Terdekat"
2. Sistem cek database: Apakah ada instansi dalam radius 5000m?
3. Jika **ADA ≥ 3 instansi**: Gunakan data dari database (tidak request Places API)
4. Jika **TIDAK ADA atau < 3**: Request ke Places API, simpan hasil ke database

## 📝 File yang Diubah

1. **backend/user/create_report.php**
   - Menghapus `libraries=places` dari Google Maps API
   - Menambahkan komentar menjelaskan kenapa dihapus

2. **backend/admin/map_dashboard.php**
   - Menambahkan tombol "Cari Instansi Darurat Terdekat"
   - Menambahkan fungsi JavaScript untuk handle request Places API
   - Menambahkan komentar menjelaskan optimasi

3. **backend/admin/api_find_instansi.php** (BARU)
   - Endpoint untuk handle request Places API
   - Implementasi cache database
   - Admin-only access
   - Batasan query (type, radius)

4. **backend/database/07_add_coordinates_to_alamat_instansi.sql** (BARU)
   - Migration script untuk menambahkan kolom koordinat

## ✅ Checklist Optimasi

- [x] Tidak ada Autocomplete
- [x] Tidak ada request Places di onLoad
- [x] Tidak ada request Places saat input berubah
- [x] Tidak ada request Places saat map digeser/di-zoom
- [x] Ada tombol manual "Cari Instansi Darurat Terdekat"
- [x] Ada cache database (cek sebelum request Places API)
- [x] Admin-only access untuk trigger Places API
- [x] API key tidak hardcoded (menggunakan konstanta dari config.php)
- [x] Komentar kode menjelaskan optimasi

## 🚀 Cara Menggunakan

1. **Jalankan Migration:**
   ```sql
   -- Jalankan di phpMyAdmin atau MySQL CLI
   SOURCE backend/database/07_add_coordinates_to_alamat_instansi.sql;
   ```

2. **Setup API Key:**
   - Edit `backend/config.php`
   - Ganti `YOUR_GOOGLE_MAPS_API_KEY` dengan API key Anda

3. **Test sebagai Admin:**
   - Login sebagai admin
   - Buka halaman "Peta Laporan"
   - Klik tombol "Cari Instansi Darurat Terdekat"
   - Sistem akan cek cache dulu, baru request Places API jika perlu

## 📚 Referensi

- [Google Places API Pricing](https://developers.google.com/maps/billing-and-pricing/pricing#places)
- [Places API Nearby Search](https://developers.google.com/maps/documentation/places/web-service/search-nearby)
- [Optimasi API Usage](https://developers.google.com/maps/documentation/places/web-service/usage-and-billing)

