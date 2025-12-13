<?php
/**
 * PROJECT ONE - GOOGLE OAUTH CALLBACK
 * Handles Google OAuth callback and user authentication
 */

session_start();
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config.php';

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', APP_URL . '/backend/auth/google_callback.php');

// Verify state token (CSRF protection)
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    header('Location: login.php?error=' . urlencode('Invalid state token'));
    exit();
}

unset($_SESSION['oauth_state']);

// Check for error from Google
if (isset($_GET['error'])) {
    header('Location: login.php?error=' . urlencode('Google login dibatalkan'));
    exit();
}

// Get authorization code
if (!isset($_GET['code'])) {
    header('Location: login.php?error=' . urlencode('Authorization code tidak ditemukan'));
    exit();
}

$code = $_GET['code'];

try {
    // Exchange code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to get access token');
    }
    
    $token_response = json_decode($response, true);
    
    if (!isset($token_response['access_token'])) {
        throw new Exception('Access token not found');
    }
    
    $access_token = $token_response['access_token'];
    
    // Get user info from Google
    $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $userinfo_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $userinfo_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to get user info');
    }
    
    $userinfo = json_decode($userinfo_response, true);
    
    if (!isset($userinfo['email'])) {
        throw new Exception('Email not found in user info');
    }
    
    $email = $userinfo['email'];
    $name = $userinfo['name'] ?? '';
    $google_id = $userinfo['id'] ?? '';
    $picture = $userinfo['picture'] ?? '';
    
    // Check if user exists
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // User exists, update Google ID if needed
        if (empty($user['google_id'])) {
            $stmt = $db->prepare("UPDATE users SET google_id = :google_id WHERE id = :id");
            $stmt->execute(['google_id' => $google_id, 'id' => $user['id']]);
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
        
    } else {
        // Auto-register new user
        $username = strtolower(str_replace(' ', '', $name));
        $username = preg_replace('/[^a-z0-9]/', '', $username);
        
        // Ensure username is unique
        $original_username = $username;
        $counter = 1;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if (!$stmt->fetch()) {
                break;
            }
            $username = $original_username . $counter;
            $counter++;
        }
        
        // Insert new user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, role, google_id, status, created_at)
            VALUES (:username, :email, :password, 'user', :google_id, 'active', NOW())
        ");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // Random password
            'google_id' => $google_id
        ]);
        
        $user_id = $db->lastInsertId();
        
        // Set session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['user_role'] = 'user';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }
    
    // Redirect based on role
    $role = $_SESSION['user_role'];
    if ($role === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
    
} catch (Exception $e) {
    error_log("Google OAuth error: " . $e->getMessage());
    header('Location: login.php?error=' . urlencode('Terjadi kesalahan saat login dengan Google'));
    exit();
}

?>

