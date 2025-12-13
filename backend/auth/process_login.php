<?php
/**
 * PROJECT ONE - LOGIN PROCESSOR
 * Handles username/email and password authentication
 */

session_start();
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Sanitize input
$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($username) || empty($password)) {
    header('Location: login.php?error=' . urlencode('Username/email dan password harus diisi'));
    exit();
}

try {
    $db = getDB();
    
    // Check if username/email exists (check without status first to see if user exists)
    // Use separate parameters to avoid any potential SQL parameter issues
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
    
    // Check if user is active
    if ($user['status'] !== 'active') {
        header('Location: login.php?error=' . urlencode('Akun Anda tidak aktif. Silakan hubungi administrator.'));
        exit();
    }
    
    // Verify password
    // Check if password field is empty or null
    if (empty($user['password'])) {
        error_log("Login error: Password field is empty for user ID: " . $user['id']);
        header('Location: login.php?error=' . urlencode('Terjadi kesalahan dengan akun Anda. Silakan hubungi administrator.'));
        exit();
    }
    
    // Debug: Log password verification attempt (remove in production)
    if (ini_get('display_errors')) {
        error_log("Login attempt - User: " . $username . ", Password length: " . strlen($password));
        error_log("Stored hash: " . substr($user['password'], 0, 20) . "...");
    }
    
    if (!password_verify($password, $user['password'])) {
        // Additional debug info
        if (ini_get('display_errors')) {
            error_log("Password verification FAILED for user: " . $username);
        }
        header('Location: login.php?error=' . urlencode('Username/email atau password salah'));
        exit();
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Remember me functionality
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store remember token in database
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
    
    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $stmt->execute(['id' => $user['id']]);
    
    // Redirect based on role
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

