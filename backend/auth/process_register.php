<?php
/**
 * PROJECT ONE - PROSES PENDAFTARAN
 * Menangani pendaftaran pengguna (peran otomatis disetel ke 'user')
 */

session_start();
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config.php';

// Periksa apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// Bersihkan dan ambil input
$fullname = sanitizeInput($_POST['fullname'] ?? '');
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validasi
$errors = [];

// Validasi nama lengkap
if (empty($fullname)) {
    $errors[] = 'Nama lengkap wajib diisi';
} elseif (strlen($fullname) < 3) {
    $errors[] = 'Nama lengkap minimal 3 karakter';
} elseif (strlen($fullname) > 100) {
    $errors[] = 'Nama lengkap maksimal 100 karakter';
}

// Validasi username
if (empty($username)) {
    $errors[] = 'Username wajib diisi';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username minimal 3 karakter';
} elseif (strlen($username) > 20) {
    $errors[] = 'Username maksimal 20 karakter';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username hanya boleh mengandung huruf, angka, dan underscore';
}

// Validasi email
if (empty($email)) {
    $errors[] = 'Email wajib diisi';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email maksimal 100 karakter';
}

// Validasi password
if (empty($password)) {
    $errors[] = 'Password wajib diisi';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password minimal 8 karakter';
} elseif (strlen($password) > 255) {
    $errors[] = 'Password terlalu panjang';
}

// Validasi konfirmasi password
if (empty($confirm_password)) {
    $errors[] = 'Konfirmasi password wajib diisi';
} elseif ($password !== $confirm_password) {
    $errors[] = 'Password dan konfirmasi password tidak cocok';
}

// Jika ada error validasi, arahkan kembali
if (!empty($errors)) {
    $error_msg = implode('. ', $errors);
    $params = http_build_query([
        'error' => $error_msg,
        'fullname' => $fullname,
        'username' => $username,
        'email' => $email
    ]);
    header('Location: register.php?' . $params);
    exit();
}

try {
    $db = getDB();
    
    // Periksa apakah username sudah ada
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        $params = http_build_query([
            'error' => 'Username sudah digunakan. Silakan pilih username lain.',
            'fullname' => $fullname,
            'username' => '',
            'email' => $email
        ]);
        header('Location: register.php?' . $params);
        exit();
    }
    
    // Periksa apakah email sudah ada
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $params = http_build_query([
            'error' => 'Email sudah terdaftar. Silakan gunakan email lain atau login.',
            'fullname' => $fullname,
            'username' => $username,
            'email' => ''
        ]);
        header('Location: register.php?' . $params);
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Periksa apakah kolom fullname ada, jika tidak, masukkan tanpa kolom tersebut
    try {
        $columns_check = $db->query("SHOW COLUMNS FROM users LIKE 'fullname'");
        $has_fullname = $columns_check->rowCount() > 0;
    } catch (PDOException $e) {
        // Jika query gagal, asumsikan kolom tidak ada
        $has_fullname = false;
    }
    
    // Masukkan pengguna baru ke tabel users
    // CATATAN: Tabel users tidak memiliki kolom 'role' karena admin dan user sudah dipisah
    // Semua yang register di sini otomatis adalah user biasa (bukan admin)
    // Admin ada di tabel terpisah (tabel admin)
    if ($has_fullname) {
        $stmt = $db->prepare("
            INSERT INTO users (fullname, username, email, password, status, created_at)
            VALUES (:fullname, :username, :email, :password, 'active', NOW())
        ");
        
        $stmt->execute([
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password
        ]);
    } else {
        // Fallback: masukkan tanpa fullname jika kolom tidak ada
        // CATATAN: Ini seharusnya tidak terjadi jika database sudah di-migrate dengan benar
        // Tabel users di schema baru (00_single_database.sql) sudah memiliki kolom fullname
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, status, created_at)
            VALUES (:username, :email, :password, 'active', NOW())
        ");
        
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password
        ]);
    }
    
    // Pendaftaran berhasil
    header('Location: login.php?success=' . urlencode('Registrasi berhasil! Silakan login dengan akun Anda.'));
    exit();
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("SQL State: " . $e->errorInfo[0]);
    
    // Periksa error entri duplikat
    if ($e->getCode() == 23000 || (isset($e->errorInfo[0]) && $e->errorInfo[0] == '23000')) {
        // Entri duplikat (username atau email)
        $params = http_build_query([
            'error' => 'Username atau email sudah terdaftar. Silakan gunakan yang lain.',
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email
        ]);
        header('Location: register.php?' . $params);
    } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
        // Kolom tidak ada - kemungkinan database belum di-migrate dengan benar
        // CATATAN: migration_add_fullname.sql sudah tidak digunakan
        // Gunakan 00_single_database.sql untuk setup database baru
        $params = http_build_query([
            'error' => 'Database belum diupdate. Silakan jalankan 00_single_database.sql terlebih dahulu untuk setup database baru.',
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email
        ]);
        header('Location: register.php?' . $params);
    } else {
        // Error database lainnya - tampilkan pesan lebih spesifik di development
        $error_msg = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        if (ini_get('display_errors')) {
            // Hanya tampilkan error detail di mode development
            $error_msg = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
        header('Location: register.php?error=' . urlencode($error_msg));
    }
    exit();
}

?>

