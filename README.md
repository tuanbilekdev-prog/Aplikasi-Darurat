# Project One - Aplikasi Pelaporan Darurat

Landing page modern dan futuristik untuk aplikasi pelaporan darurat.

## Struktur Proyek

```
Aplikasi-Darurat/
├── backend/
│   ├── auth/
│   │   ├── login.php              # Halaman login
│   │   ├── process_login.php      # Proses login normal
│   │   ├── google_login.php       # Initiate Google OAuth
│   │   ├── google_callback.php    # Handle Google OAuth callback
│   │   ├── logout.php             # Logout handler
│   │   └── README.md              # Dokumentasi auth
│   ├── database/
│   │   ├── connection.php         # Database connection
│   │   └── schema.sql            # Database schema
│   ├── config.php                 # Konfigurasi aplikasi
│   ├── auth.php                   # Authentication handler
│   └── database.php               # Database connection (legacy)
└── frontend/
    ├── index.php                  # Landing page utama
    ├── partials/
    │   ├── navbar.php             # Navigation bar
    │   └── footer.php             # Footer
    └── assets/
        ├── css/
        │   ├── style.css          # Stylesheet utama
        │   └── auth.css          # Stylesheet auth pages
        ├── js/
        │   ├── main.js           # JavaScript utama
        │   └── auth.js           # JavaScript auth pages
        └── img/                   # Folder untuk gambar
```

## Fitur

- ✅ Landing page modern dengan desain futuristik
- ✅ Responsive design (mobile-friendly)
- ✅ Gradient background yang halus
- ✅ Glassmorphism effects
- ✅ Smooth scroll animations
- ✅ Sistem login dengan username/email & password
- ✅ Login dengan Google OAuth 2.0
- ✅ Struktur backend & frontend terpisah
- ✅ Siap untuk integrasi login & session PHP

## Teknologi

- **Backend**: PHP (native, tanpa framework)
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Database**: MySQL/MariaDB
- **Font**: Inter (Google Fonts)
- **Icons**: SVG inline

## Instalasi

1. Pastikan XAMPP sudah terinstall dan berjalan
2. Copy folder proyek ke `htdocs`:
   ```
   C:\xampp\htdocs\Aplikasi-Darurat
   ```
3. Setup database:
   ```sql
   -- Import backend/database/schema.sql ke phpMyAdmin
   ```
4. Konfigurasi database di `backend/database/connection.php`
5. Buka browser dan akses:
   ```
   http://localhost/Aplikasi-Darurat/frontend
   ```
   atau halaman login:
   ```
   http://localhost/Aplikasi-Darurat/backend/auth/login.php
   ```

## Warna

- **Primary (Emergency Blue)**: `#0A2540`
- **Secondary (Emergency Red)**: `#E63946`
- **Accent**: `#4DA3FF`
- **Background**: `#F8FAFC`
- **Text**: `#1F2937`

## Struktur Folder

### Backend (`/backend`)
- `auth/` - Sistem autentikasi (login, logout, Google OAuth)
- `database/` - Database connection & schema
- `config.php` - Konfigurasi aplikasi & helper functions
- `auth.php` - Authentication handler (legacy)
- `database.php` - Database connection (legacy)

### Frontend (`/frontend`)
- `index.php` - Landing page utama
- `partials/` - Komponen reusable (navbar, footer)
- `assets/` - CSS, JavaScript, dan gambar

## Pengembangan Selanjutnya

- [ ] Implementasi sistem register
- [ ] Dashboard user
- [ ] Dashboard admin
- [ ] Halaman laporan darurat
- [ ] Sistem notifikasi real-time

## Catatan

- Semua file backend berada di folder `backend/`
- Semua file frontend berada di folder `frontend/`
- Struktur hanya terdiri dari 2 folder utama: `backend` dan `frontend`
- Akses aplikasi langsung ke `frontend/index.php`
- Login page: `backend/auth/login.php`
- Struktur siap untuk pengembangan lebih lanjut

## Lisensi

Proyek kuliah - Project One
