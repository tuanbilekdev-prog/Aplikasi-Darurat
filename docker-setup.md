# PROJECT ONE - Docker Setup Guide

Panduan untuk menjalankan aplikasi Aplikasi-Darurat menggunakan Docker.

## Prerequisites

- Docker Desktop (Windows/Mac) atau Docker Engine + Docker Compose (Linux)
- Git (untuk clone repository)

## Quick Start

### 1. Setup Environment Variables

Buat file `.env` dari template:

```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan dengan konfigurasi Anda:

```env
APP_URL=http://localhost:8080
DB_HOST=db
DB_NAME=emergency_system
DB_USER=root
DB_PASS=rootpassword
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
OPENAI_API_KEY=your_openai_api_key
```

### 2. Build dan Run Docker Containers

```bash
# Build images
docker-compose build

# Start containers
docker-compose up -d
```

### 3. Setup Database

Database akan otomatis di-initialize dari file SQL di `backend/database/00_single_database.sql`.

Untuk import data sample (opsional):

```bash
docker-compose exec db mysql -u root -prootpassword emergency_system < backend/database/05_insert_sample_data.sql
```

### 4. Setup Config File

Copy `backend/config.example.php` ke `backend/config.php`:

```bash
cp backend/config.example.php backend/config.php
```

Edit `backend/config.php` dan sesuaikan konfigurasi database dan API keys.

**Atau** jika menggunakan environment variables, config akan dibuat otomatis.

### 5. Akses Aplikasi

- **Aplikasi Web**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Server: `db`
  - Username: `root`
  - Password: (sesuai `DB_PASS` di `.env`)

## Docker Commands

### Menjalankan Containers

```bash
# Start containers (detached mode)
docker-compose up -d

# Start dengan melihat logs
docker-compose up

# Stop containers
docker-compose stop

# Stop dan remove containers
docker-compose down

# Stop, remove containers, dan volumes (HAPUS DATA!)
docker-compose down -v
```

### View Logs

```bash
# Semua services
docker-compose logs

# Service specific
docker-compose logs web
docker-compose logs db

# Follow logs
docker-compose logs -f web
```

### Execute Commands in Container

```bash
# PHP container
docker-compose exec web bash
docker-compose exec web php -v

# MySQL container
docker-compose exec db bash
docker-compose exec db mysql -u root -prootpassword emergency_system
```

### Rebuild Containers

```bash
# Rebuild setelah perubahan Dockerfile
docker-compose build --no-cache

# Rebuild dan restart
docker-compose up -d --build
```

## Services

### 1. Web (PHP Apache)
- **Port**: 8080
- **Image**: Custom (PHP 8.2 + Apache)
- **Volumes**: 
  - `./backend` → `/var/www/html/backend`
  - `./frontend` → `/var/www/html/frontend`
  - `./uploads` → `/var/www/html/uploads`

### 2. Database (MySQL 8.0)
- **Port**: 3307 (external), 3306 (internal)
- **Image**: mysql:8.0
- **Database**: `emergency_system`
- **Volumes**: 
  - `db_data` (persistent storage)
  - SQL init files

### 3. phpMyAdmin (Optional)
- **Port**: 8081
- **Image**: phpmyadmin:latest
- **Access**: http://localhost:8081

## Troubleshooting

### Port Already in Use

Jika port 8080, 8081, atau 3307 sudah digunakan, edit `docker-compose.yml`:

```yaml
ports:
  - "8080:80"  # Ganti 8080 dengan port lain, misal 8082
```

### Database Connection Error

1. Pastikan container `db` sudah running: `docker-compose ps`
2. Check logs: `docker-compose logs db`
3. Pastikan `DB_HOST=db` di `.env` (bukan `localhost`)
4. Pastikan password di `.env` sesuai dengan MySQL

### Permission Denied (Uploads)

```bash
# Set permissions
docker-compose exec web chmod -R 775 /var/www/html/uploads
docker-compose exec web chown -R www-data:www-data /var/www/html/uploads
```

### Database Not Initialized

Jika database belum ter-initialize:

```bash
# Check init logs
docker-compose logs db | grep "docker-entrypoint-initdb.d"

# Manual import
docker-compose exec db mysql -u root -prootpassword -e "CREATE DATABASE IF NOT EXISTS emergency_system;"
docker-compose exec -T db mysql -u root -prootpassword emergency_system < backend/database/00_single_database.sql
```

### Clear Everything and Start Fresh

```bash
# Stop dan remove semua (termasuk volumes)
docker-compose down -v

# Remove images (optional)
docker-compose down --rmi all

# Rebuild dan start
docker-compose up -d --build
```

## Production Deployment

Untuk production, disarankan:

1. **Update `.env`** dengan konfigurasi production:
   - `APP_URL` → domain production
   - `DB_PASS` → password yang kuat
   - API keys production

2. **Update `docker-compose.yml`**:
   - Gunakan environment variables untuk secrets
   - Consider menggunakan Docker secrets
   - Setup reverse proxy (nginx) di depan
   - Enable SSL/TLS

3. **Security**:
   - Jangan expose MySQL port ke public
   - Gunakan strong passwords
   - Keep Docker images updated
   - Enable firewall rules

4. **Backup Database**:
   ```bash
   docker-compose exec db mysqldump -u root -prootpassword emergency_system > backup.sql
   ```

## File Structure

```
.
├── Dockerfile              # PHP Apache image definition
├── docker-compose.yml      # Docker Compose configuration
├── .dockerignore          # Files to ignore in Docker build
├── .env.example           # Environment variables template
├── .env                   # Environment variables (create from .env.example)
├── docker-setup.md        # This file
└── docker-init.sh         # Initialization script (optional)
```

## Support

Jika ada masalah, check:
1. Docker logs: `docker-compose logs`
2. Container status: `docker-compose ps`
3. Database connectivity: `docker-compose exec db mysql -u root -p`

