<?php
/**
 * PROJECT ONE - PROSES MASUK
 * Menangani autentikasi username/email dan password
 */

session_start();
// Load Docker config jika di Docker environment, jika tidak load config.php biasa
if (file_exists(__DIR__ . '/../config.docker.php') && getenv('DB_HOST') === 'db') {
    require_once __DIR__ . '/../config.docker.php';
} else {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../database/connection.php';

// Periksa apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Bersihkan input
$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validasi input
if (empty($username) || empty($password)) {
    header('Location: login.php?error=' . urlencode('Username/email dan password harus diisi'));
    exit();
}

try {
    // Single database: emergency_system
    $db = getDB();
    
    $user = null;
    $user_type = null; // 'admin' atau 'user'
    
    // Cek di tabel admin terlebih dahulu
    $stmt = $db->prepare("
        SELECT id, username, email, password, role, status, fullname, instansi_id
        FROM admin 
        WHERE username = :username OR email = :email
        LIMIT 1
    ");
    $stmt->execute([
        'username' => $username,
        'email' => $username
    ]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $user = $admin;
        $user_type = 'admin';
    } else {
        // Jika tidak ada di admin, cek di tabel users
        $stmt = $db->prepare("
            SELECT id, username, email, password, status, fullname
            FROM users 
            WHERE username = :username OR email = :email
            LIMIT 1
        ");
        $stmt->execute([
            'username' => $username,
            'email' => $username
        ]);
        $user_data = $stmt->fetch();
        
        if ($user_data) {
            $user = $user_data;
            $user['role'] = 'user'; // Set default role untuk user
            $user_type = 'user';
        }
    }
    
    if (!$user) {
        header('Location: login.php?error=' . urlencode('Username/email atau password salah'));
        exit();
    }
    
    // Periksa apakah pengguna aktif
    if ($user['status'] !== 'active') {
        header('Location: login.php?error=' . urlencode('Akun Anda tidak aktif. Silakan hubungi administrator.'));
        exit();
    }
    
    // Verifikasi password
    // Periksa apakah field password kosong atau null
    if (empty($user['password'])) {
        error_log("Login error: Password field is empty for user ID: " . $user['id']);
        header('Location: login.php?error=' . urlencode('Terjadi kesalahan dengan akun Anda. Silakan hubungi administrator.'));
        exit();
    }
    
    // Debug: Catat upaya verifikasi password
    error_log("=== LOGIN DEBUG ===");
    error_log("Username/Email: " . $username);
    error_log("User found: " . ($user ? "YES" : "NO"));
    if ($user) {
        error_log("User ID: " . $user['id']);
        error_log("User Type: " . $user_type);
        error_log("User Status: " . $user['status']);
        error_log("Password field empty: " . (empty($user['password']) ? "YES" : "NO"));
        error_log("Stored hash preview: " . substr($user['password'], 0, 30) . "...");
        error_log("Input password length: " . strlen($password));
    }
    
    if (!password_verify($password, $user['password'])) {
        error_log("Password verification FAILED for user: " . $username);
        error_log("Password verify result: " . (password_verify($password, $user['password']) ? "TRUE" : "FALSE"));
        header('Location: login.php?error=' . urlencode('Username/email atau password salah'));
        exit();
    }
    
    error_log("Password verification SUCCESS for user: " . $username);
    
    // Setel sesi
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Simpan informasi tambahan berdasarkan tipe user
    if ($user_type === 'admin') {
        $_SESSION['admin_instansi_id'] = $user['instansi_id'] ?? null;
        $_SESSION['fullname'] = $user['fullname'] ?? '';
        $table_name = 'admin';
    } else {
        $_SESSION['fullname'] = $user['fullname'] ?? '';
        $table_name = 'users';
    }
    
    // Fungsi ingat saya
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 hari
        
        // Simpan token ingat saya di database
        $stmt = $db->prepare("
            UPDATE {$table_name} 
            SET remember_token = :token, remember_expiry = :expiry 
            WHERE id = :id
        ");
        $stmt->execute([
            'token' => password_hash($token, PASSWORD_DEFAULT),
            'expiry' => $expiry,
            'id' => $user['id']
        ]);
        
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }
    
    // Perbarui waktu masuk terakhir
    $stmt = $db->prepare("UPDATE {$table_name} SET last_login = NOW() WHERE id = :id");
    $stmt->execute(['id' => $user['id']]);
    
    // Arahkan berdasarkan peran
    // Admin bisa memiliki role: 'super_admin', 'admin', atau 'operator'
    if (in_array($user['role'], ['super_admin', 'admin', 'operator'])) {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    header('Location: login.php?error=' . urlencode('Terjadi kesalahan sistem. Silakan coba lagi.'));
    exit();
}

