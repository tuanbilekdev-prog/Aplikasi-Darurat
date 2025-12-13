<?php
/**
 * PROJECT ONE - REGISTRATION PROCESSOR
 * Handles user registration (role automatically set to 'user')
 */

session_start();
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// Sanitize and get input
$fullname = sanitizeInput($_POST['fullname'] ?? '');
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

// Validate fullname
if (empty($fullname)) {
    $errors[] = 'Nama lengkap wajib diisi';
} elseif (strlen($fullname) < 3) {
    $errors[] = 'Nama lengkap minimal 3 karakter';
} elseif (strlen($fullname) > 100) {
    $errors[] = 'Nama lengkap maksimal 100 karakter';
}

// Validate username
if (empty($username)) {
    $errors[] = 'Username wajib diisi';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username minimal 3 karakter';
} elseif (strlen($username) > 20) {
    $errors[] = 'Username maksimal 20 karakter';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username hanya boleh mengandung huruf, angka, dan underscore';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email wajib diisi';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email maksimal 100 karakter';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Password wajib diisi';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password minimal 8 karakter';
} elseif (strlen($password) > 255) {
    $errors[] = 'Password terlalu panjang';
}

// Validate confirm password
if (empty($confirm_password)) {
    $errors[] = 'Konfirmasi password wajib diisi';
} elseif ($password !== $confirm_password) {
    $errors[] = 'Password dan konfirmasi password tidak cocok';
}

// If there are validation errors, redirect back
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
    
    // Check if username already exists
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
    
    // Check if email already exists
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
    
    // Check if fullname column exists, if not, insert without it
    try {
        $columns_check = $db->query("SHOW COLUMNS FROM users LIKE 'fullname'");
        $has_fullname = $columns_check->rowCount() > 0;
    } catch (PDOException $e) {
        // If query fails, assume column doesn't exist
        $has_fullname = false;
    }
    
    // Insert new user with role automatically set to 'user'
    // IMPORTANT: Role is hardcoded to 'user', never accept from form
    if ($has_fullname) {
        $stmt = $db->prepare("
            INSERT INTO users (fullname, username, email, password, role, status, created_at)
            VALUES (:fullname, :username, :email, :password, 'user', 'active', NOW())
        ");
        
        $stmt->execute([
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password
        ]);
    } else {
        // Fallback: insert without fullname if column doesn't exist
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, role, status, created_at)
            VALUES (:username, :email, :password, 'user', 'active', NOW())
        ");
        
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password
        ]);
    }
    
    // Registration successful
    header('Location: login.php?success=' . urlencode('Registrasi berhasil! Silakan login dengan akun Anda.'));
    exit();
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    error_log("SQL State: " . $e->errorInfo[0]);
    
    // Check for duplicate entry error
    if ($e->getCode() == 23000 || (isset($e->errorInfo[0]) && $e->errorInfo[0] == '23000')) {
        // Duplicate entry (username or email)
        $params = http_build_query([
            'error' => 'Username atau email sudah terdaftar. Silakan gunakan yang lain.',
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email
        ]);
        header('Location: register.php?' . $params);
    } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
        // Column doesn't exist - likely fullname column is missing
        $params = http_build_query([
            'error' => 'Database belum diupdate. Silakan jalankan migration_add_fullname.sql terlebih dahulu.',
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email
        ]);
        header('Location: register.php?' . $params);
    } else {
        // Other database error - show more specific message in development
        $error_msg = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        if (ini_get('display_errors')) {
            // Only show detailed error in development mode
            $error_msg = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
        header('Location: register.php?error=' . urlencode($error_msg));
    }
    exit();
}

?>

