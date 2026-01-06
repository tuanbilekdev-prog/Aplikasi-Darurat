# Setup Cloudflare Tunnel untuk projectone.space

## ✅ Yang Sudah Dikerjakan

1. ✅ Tunnel dibuat: `projectone-tunnel` (ID: `02f32673-8545-4375-aae2-989ebc7f6899`)
2. ✅ File konfigurasi dibuat: `cloudflare-config.yml`
3. ✅ Script untuk menjalankan tunnel: `start-cloudflare-tunnel.bat`

## ⚠️ PENTING: Setup DNS di Cloudflare Dashboard

Karena domain `projectone.space` perlu dikonfigurasi manual di Cloudflare Dashboard, ikuti langkah berikut:

### Langkah 1: Pastikan Domain Terhubung ke Cloudflare

1. Buka [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Pastikan domain `projectone.space` sudah ditambahkan ke akun Cloudflare Anda
3. Jika belum, tambahkan domain dengan mengikuti wizard Cloudflare

### Langkah 2: Setup DNS Records

Buka **DNS** → **Records** di Cloudflare Dashboard, lalu tambahkan:

#### Record 1: Main Domain
- **Type**: `CNAME`
- **Name**: `@` (atau `projectone.space`)
- **Target**: `02f32673-8545-4375-aae2-989ebc7f6899.cfargotunnel.com`
- **Proxy status**: 🟠 **Proxied** (ON - orange cloud)
- **TTL**: Auto

#### Record 2: www Subdomain
- **Type**: `CNAME`
- **Name**: `www`
- **Target**: `02f32673-8545-4375-aae2-989ebc7f6899.cfargotunnel.com`
- **Proxy status**: 🟠 **Proxied** (ON - orange cloud)
- **TTL**: Auto

#### Record 3: phpMyAdmin Subdomain
- **Type**: `CNAME`
- **Name**: `phpmyadmin`
- **Target**: `02f32673-8545-4375-aae2-989ebc7f6899.cfargotunnel.com`
- **Proxy status**: 🟠 **Proxied** (ON - orange cloud)
- **TTL**: Auto

### Langkah 3: Verifikasi DNS

Setelah menambahkan DNS records, tunggu beberapa menit untuk propagasi DNS (biasanya 1-5 menit).

## 🚀 Cara Menjalankan

### Opsi 1: Menggunakan Script (Paling Mudah)

1. Pastikan Docker containers berjalan:
   ```powershell
   docker-compose up -d
   ```

2. Jalankan script:
   ```powershell
   .\start-cloudflare-tunnel.bat
   ```

### Opsi 2: Manual Command

```powershell
cloudflared tunnel --config cloudflare-config.yml run projectone-tunnel
```

## 📋 Konfigurasi

File `cloudflare-config.yml` mengatur:

- **projectone.space** → `http://localhost:8080` (Aplikasi utama)
- **www.projectone.space** → `http://localhost:8080` (Aplikasi utama)
- **phpmyadmin.projectone.space** → `http://localhost:8081` (phpMyAdmin)

## 🔒 Keamanan

### Untuk phpMyAdmin (Sangat Disarankan):

1. **Setup Cloudflare Access (Zero Trust)**:
   - Buka Cloudflare Dashboard → Zero Trust → Access → Applications
   - Klik "Add an application"
   - Pilih "Self-hosted"
   - Application name: `phpMyAdmin`
   - Application domain: `phpmyadmin.projectone.space`
   - Tambahkan policy dengan email yang diizinkan
   - Save

2. **Atau gunakan password kuat** untuk MySQL root user

## 🧪 Testing

Setelah tunnel berjalan dan DNS sudah terkonfigurasi:

1. **Test aplikasi utama**:
   - Buka: `https://projectone.space`
   - Harusnya muncul landing page aplikasi

2. **Test phpMyAdmin**:
   - Buka: `https://phpmyadmin.projectone.space`
   - Harusnya muncul halaman login phpMyAdmin

## ⚙️ Menjalankan sebagai Windows Service (Opsional)

Agar tunnel berjalan otomatis saat Windows start:

```powershell
# Install sebagai service
cloudflared service install

# Jalankan service
net start cloudflared

# Stop service
net stop cloudflared

# Uninstall service
cloudflared service uninstall
```

**Catatan**: Jika menggunakan service, pastikan path ke `cloudflare-config.yml` sudah benar di service configuration.

## 🔍 Troubleshooting

### Tunnel tidak bisa connect
- Pastikan Docker containers berjalan (`docker-compose ps`)
- Pastikan aplikasi bisa diakses di `http://localhost:8080`
- Pastikan phpMyAdmin bisa diakses di `http://localhost:8081`

### DNS tidak resolve
- Pastikan DNS records sudah ditambahkan di Cloudflare Dashboard
- Tunggu beberapa menit untuk propagasi DNS
- Cek dengan: `nslookup projectone.space`

### SSL Certificate Error
- Cloudflare Tunnel otomatis menyediakan SSL
- Pastikan proxy status ON (orange cloud) di DNS records

### Domain tidak terhubung
- Pastikan domain `projectone.space` sudah ditambahkan ke Cloudflare account
- Pastikan nameserver domain sudah diarahkan ke Cloudflare

## 📝 Catatan

- **Laptop harus menyala** dan terhubung internet agar tunnel aktif
- **Tunnel harus berjalan** agar aplikasi bisa diakses dari internet
- **Docker containers harus berjalan** agar aplikasi tersedia di localhost

## 🆘 Bantuan

Jika ada masalah:
1. Cek log tunnel untuk error messages
2. Pastikan semua service berjalan
3. Verifikasi DNS records di Cloudflare Dashboard
4. Pastikan firewall tidak memblokir koneksi

