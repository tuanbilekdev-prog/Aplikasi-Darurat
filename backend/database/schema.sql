-- PROJECT ONE - DATABASE SCHEMA
-- Create database and users table

CREATE DATABASE IF NOT EXISTS project_one_db;
USE project_one_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    google_id VARCHAR(100) NULL,
    remember_token VARCHAR(255) NULL,
    remember_expiry INT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports Table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    category ENUM('kecelakaan', 'kebakaran', 'medis', 'kejahatan', 'bencana', 'lainnya') NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    urgent TINYINT(1) DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    admin_notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- NOTE: Generate password hash using PHP: password_hash('your_password', PASSWORD_DEFAULT)
-- Example for password 'admin123':
-- INSERT INTO users (username, email, password, role, status) 
-- VALUES (
--     'admin',
--     'admin@projectone.id',
--     '$2y$10$YOUR_GENERATED_HASH_HERE',
--     'admin',
--     'active'
-- );

