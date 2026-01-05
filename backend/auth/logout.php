<?php
/**
 * PROJECT ONE - PENANGANAN KELUAR
 * Menangani keluar pengguna dan pembersihan sesi
 */

session_start();
require_once __DIR__ . '/../database/connection.php';

// Hapus token ingat saya jika ada
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

// Hancurkan sesi
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// Arahkan ke halaman masuk
header('Location: login.php?success=' . urlencode('Anda telah berhasil keluar'));
exit();

