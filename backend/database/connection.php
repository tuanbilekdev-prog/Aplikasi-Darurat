<?php
/**
 * PROJECT ONE - KONEKSI DATABASE
 * Penanganan koneksi database yang aman
 * 
 * ARSITEKTUR: Single Database (emergency_system)
 * - Semua tabel (admin & user) berada dalam satu database
 * - Admin dan User dipisahkan menggunakan tabel terpisah
 */

// Konfigurasi Database - Gunakan konstanta dari config.php jika belum didefinisikan
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// Nama database (default: emergency_system)
if (!defined('DB_NAME')) {
    define('DB_NAME', 'emergency_system');
}

class Database {
    private static $instance = null;
    private $connection = null;
    
    /**
     * Dapatkan instance database (Singleton Pattern)
     * 
     * @return Database Instance database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Konstruktor privat
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please contact administrator.");
        }
    }
    
    /**
     * Dapatkan koneksi database
     * 
     * @return PDO Koneksi database
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Cegah kloning
     */
    private function __clone() {}
    
    /**
     * Cegah unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// ============================================
// FUNGSI BANTUAN
// ============================================

/**
 * Dapatkan koneksi database (untuk semua tabel)
 * 
 * @return PDO Koneksi ke emergency_system
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Alias untuk getDB() - untuk backward compatibility
 * Semua query admin dan user menggunakan database yang sama
 * 
 * @return PDO Koneksi ke emergency_system
 */
function getAdminDB() {
    return Database::getInstance()->getConnection();
}

?>
