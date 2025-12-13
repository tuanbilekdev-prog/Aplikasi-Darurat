<?php
/**
 * PROJECT ONE - LOGOUT HANDLER
 * Handles user logout and session cleanup
 */

session_start();
require_once __DIR__ . '/../database/connection.php';

// Clear remember token if exists
if (isset($_COOKIE['remember_token'])) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE remember_token IS NOT NULL");
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Logout error: " . $e->getMessage());
    }
    
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Destroy session
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// Redirect to login
header('Location: login.php?success=' . urlencode('Anda telah berhasil keluar'));
exit();

?>

