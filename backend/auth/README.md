# Project One - Authentication System

Sistem autentikasi modern untuk aplikasi pelaporan darurat Project One.

## Fitur

- ✅ Login dengan Username/Email & Password
- ✅ Login dengan Google OAuth 2.0
- ✅ Password hashing dengan `password_hash()`
- ✅ Session-based authentication
- ✅ Remember me functionality
- ✅ Role-based access (user & admin)
- ✅ Auto-register untuk Google login

## Instalasi

### 1. Database Setup

Jalankan file SQL untuk membuat database dan tabel:

```sql
-- Import backend/database/schema.sql ke phpMyAdmin atau MySQL
```

Atau jalankan via command line:

```bash
mysql -u root -p < backend/database/schema.sql
```

### 2. Konfigurasi Database

Edit file `backend/database/connection.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'project_one_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Setup Google OAuth

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang ada
3. Enable Google+ API
4. Buat OAuth 2.0 Client ID
5. Set Authorized redirect URI: `http://localhost/Aplikasi-Darurat/backend/auth/google_callback.php`
6. Copy Client ID dan Client Secret

Edit file `backend/auth/google_login.php` dan `backend/auth/google_callback.php`:

```php
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
```

### 4. Buat Admin User

Jalankan query SQL berikut untuk membuat admin:

```sql
INSERT INTO users (username, email, password, role, status) 
VALUES (
    'admin',
    'admin@projectone.id',
    '$2y$10$YourPasswordHashHere',
    'admin',
    'active'
);
```

Untuk generate password hash, gunakan:

```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

## Struktur File

```
backend/
├── auth/
│   ├── login.php              # Halaman login
│   ├── process_login.php      # Proses login normal
│   ├── google_login.php       # Initiate Google OAuth
│   ├── google_callback.php    # Handle Google OAuth callback
│   ├── logout.php             # Logout handler
│   └── README.md              # Dokumentasi ini
└── database/
    ├── connection.php         # Database connection
    └── schema.sql            # Database schema

frontend/assets/
├── css/
│   └── auth.css              # Stylesheet untuk auth pages
└── js/
    └── auth.js               # JavaScript untuk auth pages
```

## Penggunaan

### Login Normal

1. User mengisi username/email dan password
2. Sistem memverifikasi dengan database
3. Session dibuat
4. Redirect ke dashboard sesuai role

### Login Google

1. User klik "Masuk dengan Google"
2. Redirect ke Google OAuth
3. User authorize aplikasi
4. Callback mengambil email & name
5. Jika email belum ada, auto-register sebagai 'user'
6. Session dibuat
7. Redirect ke dashboard

## Keamanan

- ✅ Password di-hash dengan `password_hash()`
- ✅ Prepared statements untuk mencegah SQL Injection
- ✅ Input sanitization
- ✅ CSRF protection dengan state token (Google OAuth)
- ✅ Session security
- ✅ Remember token hashing

## Catatan

- Default admin password harus diubah setelah setup
- Google OAuth credentials harus diisi untuk fitur Google login
- Pastikan session path dan cookie settings sesuai kebutuhan

## Troubleshooting

### Error: Database connection failed
- Pastikan MySQL/MariaDB berjalan
- Cek kredensial database di `backend/database/connection.php`
- Pastikan database `project_one_db` sudah dibuat

### Google Login tidak bekerja
- Pastikan Google OAuth credentials sudah diisi
- Cek redirect URI di Google Cloud Console
- Pastikan Google+ API sudah di-enable

### Session tidak tersimpan
- Cek `session.save_path` di php.ini
- Pastikan folder session writable
- Cek session_start() di setiap file

