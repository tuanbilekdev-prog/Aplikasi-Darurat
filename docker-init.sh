#!/bin/bash
# PROJECT ONE - Docker Initialization Script
# Script untuk setup awal setelah container dibuat

echo "Waiting for MySQL to be ready..."
until mysqladmin ping -h db -u root -prootpassword --silent; do
  echo "MySQL is unavailable - sleeping"
  sleep 2
done

echo "MySQL is up - executing commands"

# Create config.php from config.example.php if not exists
if [ ! -f backend/config.php ]; then
    echo "Creating backend/config.php from config.example.php..."
    cp backend/config.example.php backend/config.php
    
    # Update database config from environment variables
    sed -i "s|define('APP_URL', '.*');|define('APP_URL', '${APP_URL:-http://localhost:8080}');|g" backend/config.php
    sed -i "s|define('DB_HOST', '.*');|define('DB_HOST', '${DB_HOST:-db}');|g" backend/config.php
    sed -i "s|define('DB_NAME', '.*');|define('DB_NAME', '${DB_NAME:-emergency_system}');|g" backend/config.php
    sed -i "s|define('DB_USER', '.*');|define('DB_USER', '${DB_USER:-root}');|g" backend/config.php
    sed -i "s|define('DB_PASS', '.*');|define('DB_PASS', '${DB_PASS:-rootpassword}');|g" backend/config.php
    
    if [ ! -z "$GOOGLE_CLIENT_ID" ]; then
        sed -i "s|define('GOOGLE_CLIENT_ID', '.*');|define('GOOGLE_CLIENT_ID', '${GOOGLE_CLIENT_ID}');|g" backend/config.php
    fi
    
    if [ ! -z "$GOOGLE_CLIENT_SECRET" ]; then
        sed -i "s|define('GOOGLE_CLIENT_SECRET', '.*');|define('GOOGLE_CLIENT_SECRET', '${GOOGLE_CLIENT_SECRET}');|g" backend/config.php
    fi
    
    if [ ! -z "$OPENAI_API_KEY" ]; then
        sed -i "s|define('OPENAI_API_KEY', '.*');|define('OPENAI_API_KEY', '${OPENAI_API_KEY}');|g" backend/config.php
    fi
fi

# Set permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/uploads

echo "Initialization complete!"

