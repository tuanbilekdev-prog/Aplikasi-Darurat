# Setup Google Maps API

## Cara Mendapatkan Google Maps API Key

1. **Buka Google Cloud Console**
   - Kunjungi: https://console.cloud.google.com/
   - Login dengan akun Google Anda

2. **Buat Project Baru (atau pilih project yang sudah ada)**
   - Klik dropdown project di bagian atas
   - Klik "New Project"
   - Beri nama project (contoh: "Emergency System")
   - Klik "Create"

3. **Aktifkan Google Maps JavaScript API**
   - Di sidebar, pilih "APIs & Services" > "Library"
   - Cari "Maps JavaScript API"
   - Klik dan pilih "Enable"

4. **Aktifkan Places API (untuk autocomplete alamat)**
   - Di "APIs & Services" > "Library"
   - Cari "Places API"
   - Klik dan pilih "Enable"

5. **Buat API Key**
   - Di sidebar, pilih "APIs & Services" > "Credentials"
   - Klik "Create Credentials" > "API Key"
   - Copy API key yang dihasilkan

6. **Restrict API Key (PENTING untuk keamanan)**
   - Klik pada API key yang baru dibuat
   - Di bagian "Application restrictions":
     - Pilih "HTTP referrers (web sites)"
     - Tambahkan referrer: `http://localhost/*` (untuk development)
     - Tambahkan referrer domain production Anda (untuk production)
   - Di bagian "API restrictions":
     - Pilih "Restrict key"
     - Pilih "Maps JavaScript API" dan "Places API"
   - Klik "Save"

## Cara Menggunakan API Key di Aplikasi

1. **Buka file konfigurasi**
   - Edit file: `backend/config.php`

2. **Ganti placeholder dengan API key Anda**
   ```php
   // Sebelum:
   define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');
   
   // Sesudah:
   define('GOOGLE_MAPS_API_KEY', 'AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
   ```

3. **Simpan file**

## Catatan Penting

- **Jangan commit API key ke repository publik!**
  - Gunakan `.env` file atau tambahkan `config.php` ke `.gitignore`
  - Atau gunakan environment variables

- **Untuk Production:**
  - Update referrer restrictions di Google Cloud Console
  - Tambahkan domain production Anda
  - Pertimbangkan untuk membuat API key terpisah untuk production

- **Billing:**
  - Google Maps API memiliki free tier (dengan limit)
  - Monitor penggunaan di Google Cloud Console
  - Setup billing alerts untuk menghindari biaya tak terduga

## Testing

Setelah setup API key:

1. **Test halaman form laporan:**
   - Login sebagai user
   - Buka halaman "Buat Laporan"
   - Pastikan peta muncul dan bisa diklik

2. **Test geolocation:**
   - Klik tombol "Gunakan Lokasi Saya"
   - Pastikan browser meminta izin lokasi
   - Pastikan marker muncul di peta

3. **Test admin dashboard:**
   - Login sebagai admin
   - Buka halaman "Peta Laporan"
   - Pastikan semua marker laporan muncul

## Troubleshooting

### Peta tidak muncul
- Pastikan API key sudah diisi dengan benar
- Pastikan Maps JavaScript API sudah diaktifkan
- Cek browser console untuk error message
- Pastikan referrer restrictions sudah diatur dengan benar

### Error "This API key is not authorized"
- Pastikan Maps JavaScript API sudah diaktifkan
- Pastikan Places API sudah diaktifkan (jika menggunakan autocomplete)
- Cek API restrictions di Google Cloud Console

### Error "RefererNotAllowedMapError"
- Pastikan domain/referrer sudah ditambahkan di API key restrictions
- Untuk localhost, pastikan menggunakan `http://localhost/*` (bukan `https://`)

## Referensi

- [Google Maps JavaScript API Documentation](https://developers.google.com/maps/documentation/javascript)
- [Google Cloud Console](https://console.cloud.google.com/)
- [Places API Documentation](https://developers.google.com/maps/documentation/places/web-service)

