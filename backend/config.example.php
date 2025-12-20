<?php
/**
 * PROJECT ONE - FILE KONFIGURASI CONTOH
 * 
 * INSTRUKSI:
 * 1. Copy file ini menjadi config.php
 * 2. Isi dengan konfigurasi yang sesuai
 * 3. JANGAN commit config.php ke repository (sudah di .gitignore)
 * 
 * File ini adalah template untuk dokumentasi
 * JANGAN isi dengan data sensitif di sini!
 */

// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Aplikasi
define('APP_NAME', 'Project One');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Aplikasi-Darurat');
define('APP_PATH', dirname(__DIR__));

// Konfigurasi Database
// ARSITEKTUR: Single Database (emergency_system)
// Admin dan User dipisahkan menggunakan tabel terpisah, bukan database terpisah
define('DB_HOST', 'localhost');
define('DB_NAME', 'emergency_system');  // Database tunggal untuk semua data
define('DB_USER', 'root');
define('DB_PASS', '');  // GANTI dengan password database Anda
define('DB_CHARSET', 'utf8mb4');

// Konfigurasi Google Maps API
// Ganti YOUR_GOOGLE_MAPS_API_KEY dengan API key Anda dari Google Cloud Console
// Cara mendapatkan: https://console.cloud.google.com/google/maps-apis
// PENTING: Jangan commit API key ke repository!
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');

// Konfigurasi Keamanan
define('SESSION_LIFETIME', 3600); // 1 jam
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 menit

// Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// Pelaporan Error (set ke 0 di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fungsi Bantuan
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/frontend/index.php');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role) {
        header('Location: ' . APP_URL . '/frontend/index.php');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

?>

