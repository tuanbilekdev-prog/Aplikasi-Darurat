<?php
/**
 * PROJECT ONE - PENANGANAN AUTENTIKASI
 * Backend: Fungsi autentikasi pengguna
 */

require_once __DIR__ . '/config.php';

class Auth {
    
    /**
     * Periksa apakah pengguna sudah masuk
     */
    public static function isAuthenticated() {
        return isLoggedIn();
    }
    
    /**
     * Dapatkan ID pengguna saat ini
     */
    public static function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Dapatkan peran pengguna saat ini
     */
    public static function getUserRole() {
        return getUserRole();
    }
    
    /**
     * Masuk pengguna (placeholder - akan diimplementasikan dengan database)
     */
    public static function login($username, $password) {
        // TODO: Implementasikan autentikasi database
        // Ini adalah placeholder untuk implementasi di masa depan
        
        // Contoh struktur:
        // $user = self::validateUser($username, $password);
        // if ($user) {
        //     $_SESSION['user_id'] = $user['id'];
        //     $_SESSION['user_role'] = $user['role'];
        //     $_SESSION['username'] = $user['username'];
        //     return true;
        // }
        // return false;
        
        return false;
    }
    
    /**
     * Keluar pengguna
     */
    public static function logout() {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Wajibkan autentikasi
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            redirect(APP_URL . '/login.php');
        }
    }
    
    /**
     * Wajibkan peran tertentu
     */
    public static function requireRole($role) {
        self::requireAuth();
        if (self::getUserRole() !== $role) {
            redirect(APP_URL . '/index.php');
        }
    }
}

?>

