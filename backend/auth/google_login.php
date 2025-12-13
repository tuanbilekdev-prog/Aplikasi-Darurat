<?php
/**
 * PROJECT ONE - GOOGLE OAUTH LOGIN INITIATOR
 * Initiates Google OAuth 2.0 flow
 */

session_start();
require_once __DIR__ . '/../config.php';

// Google OAuth Configuration
// TODO: Replace with your actual Google OAuth credentials
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', APP_URL . '/backend/auth/google_callback.php');

// Generate state token for CSRF protection
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));

// Google OAuth URL
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account'
]);

// Redirect to Google
header('Location: ' . $auth_url);
exit();

?>

