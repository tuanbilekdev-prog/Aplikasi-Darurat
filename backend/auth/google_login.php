<?php
/**
 * PROJECT ONE - INISIATOR MASUK GOOGLE OAUTH
 * Memulai alur Google OAuth 2.0
 */

session_start();
require_once __DIR__ . '/../config.php';

// Konfigurasi Google OAuth
// TODO: Ganti dengan kredensial Google OAuth Anda yang sebenarnya
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', APP_URL . '/backend/auth/google_callback.php');

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

