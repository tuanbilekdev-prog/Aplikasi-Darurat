<?php
/**
 * PROJECT ONE - AUTHENTICATION HANDLER
 * Backend: User authentication functions
 */

require_once __DIR__ . '/config.php';

class Auth {
    
    /**
     * Check if user is logged in
     */
    public static function isAuthenticated() {
        return isLoggedIn();
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get current user role
     */
    public static function getUserRole() {
        return getUserRole();
    }
    
    /**
     * Login user (placeholder - to be implemented with database)
     */
    public static function login($username, $password) {
        // TODO: Implement database authentication
        // This is a placeholder for future implementation
        
        // Example structure:
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
     * Logout user
     */
    public static function logout() {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            redirect(APP_URL . '/login.php');
        }
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($role) {
        self::requireAuth();
        if (self::getUserRole() !== $role) {
            redirect(APP_URL . '/index.php');
        }
    }
}

?>

