<?php
/**
 * PROJECT ONE - DOCKER CONFIG OVERRIDE
 * File ini akan define semua constants untuk Docker environment
 * File ini harus di-include SEBELUM config.php untuk mencegah redefinition error
 */

// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Aplikasi
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Project One');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}
if (!defined('APP_URL')) {
    define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080');
}
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__));
}

// Konfigurasi Database - Gunakan environment variables dari Docker
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'db');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'emergency_system');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: 'rootpassword');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// Konfigurasi Keamanan
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 3600);
}
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 8);
}
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}
if (!defined('LOGIN_LOCKOUT_TIME')) {
    define('LOGIN_LOCKOUT_TIME', 900);
}

// Konfigurasi Google OAuth - Gunakan environment variables jika ada
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
}
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', APP_URL . '/backend/auth/google_callback.php');
}

// Konfigurasi OpenAI API
if (!defined('OPENAI_API_KEY')) {
    define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
}
if (!defined('OPENAI_API_ENABLED')) {
    define('OPENAI_API_ENABLED', !empty(OPENAI_API_KEY));
}

// Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// Pelaporan Error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper Functions (copy dari config.php)
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('getUserRole')) {
    function getUserRole() {
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isLoggedIn()) {
            header('Location: ' . APP_URL . '/frontend/index.php');
            exit();
        }
    }
}

if (!function_exists('requireRole')) {
    function requireRole($role) {
        requireLogin();
        if (getUserRole() !== $role) {
            header('Location: ' . APP_URL . '/frontend/index.php');
            exit();
        }
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
}

