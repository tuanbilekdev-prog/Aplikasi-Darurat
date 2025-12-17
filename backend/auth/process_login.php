<?php
/**
 * PROJECT ONE - PROSES MASUK
 * Menangani autentikasi username/email dan password
 */

session_start();
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config.php';

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
    $db = getDB();
    
    // Periksa apakah username/email ada (gunakan parameter terpisah untuk menghindari masalah parameter SQL)
    $stmt = $db->prepare("
        SELECT id, username, email, password, role, status 
        FROM users 
        WHERE username = :username OR email = :email
        LIMIT 1
    ");
    $stmt->execute([
        'username' => $username,
        'email' => $username
    ]);
    $user = $stmt->fetch();
    
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
    
    // Debug: Catat upaya verifikasi password (hapus di production)
    if (ini_get('display_errors')) {
        error_log("Login attempt - User: " . $username . ", Password length: " . strlen($password));
        error_log("Stored hash: " . substr($user['password'], 0, 20) . "...");
    }
    
    if (!password_verify($password, $user['password'])) {
        // Info debug tambahan
        if (ini_get('display_errors')) {
            error_log("Password verification FAILED for user: " . $username);
        }
        header('Location: login.php?error=' . urlencode('Username/email atau password salah'));
        exit();
    }
    
    // Setel sesi
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Fungsi ingat saya
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 hari
        
        // Simpan token ingat saya di database
        $stmt = $db->prepare("
            UPDATE users 
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
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $stmt->execute(['id' => $user['id']]);
    
    // Arahkan berdasarkan peran
    if ($user['role'] === 'admin') {
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

?>

