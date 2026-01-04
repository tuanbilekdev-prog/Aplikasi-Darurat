<?php
/**
 * PROJECT ONE - INISIATOR MASUK GOOGLE OAUTH
 * Memulai alur Google OAuth 2.0
 */

session_start();
require_once __DIR__ . '/../config.php';

// Periksa apakah Google OAuth sudah dikonfigurasi
if (!defined('GOOGLE_CLIENT_ID') || GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' || 
    !defined('GOOGLE_CLIENT_SECRET') || GOOGLE_CLIENT_SECRET === 'YOUR_GOOGLE_CLIENT_SECRET') {
    header('Location: login.php?error=' . urlencode('Google OAuth belum dikonfigurasi. Silakan set GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di config.php'));
    exit();
}

// Generate token state untuk perlindungan CSRF
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));

// URL Google OAuth
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account'
]);

// Arahkan ke Google
header('Location: ' . $auth_url);
exit();

?>

