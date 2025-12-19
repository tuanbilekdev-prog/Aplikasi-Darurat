<?php
/**
 * PROJECT ONE - KONEKSI DATABASE MULTIPLE
 * Penanganan koneksi database untuk admin_db dan user_db
 * 
 * ARSITEKTUR:
 * - admin_db: Database untuk sistem admin dan instansi
 * - user_db: Database untuk sistem user dan laporan
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

// Nama database (default: user_db untuk backward compatibility)
if (!defined('DB_NAME')) {
    define('DB_NAME', 'user_db');
}
if (!defined('DB_ADMIN_NAME')) {
    define('DB_ADMIN_NAME', 'admin_db');
}

class DatabaseMulti {
    private static $instances = [];
    private $connection = null;
    private $database = null;
    
    /**
     * Dapatkan instance database untuk database tertentu
     * 
     * @param string $db_name Nama database ('user_db' atau 'admin_db')
     * @return DatabaseMulti Instance database
     */
    public static function getInstance($db_name = 'user_db') {
        // Validasi nama database
        if (!in_array($db_name, ['user_db', 'admin_db'])) {
            throw new Exception("Database name must be 'user_db' or 'admin_db'");
        }
        
        // Buat instance jika belum ada
        if (!isset(self::$instances[$db_name])) {
            self::$instances[$db_name] = new self($db_name);
        }
        
        return self::$instances[$db_name];
    }
    
    /**
     * Konstruktor privat
     * 
     * @param string $db_name Nama database
     */
    private function __construct($db_name) {
        $this->database = $db_name;
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . $db_name . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed ({$db_name}): " . $e->getMessage());
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
     * Dapatkan nama database
     * 
     * @return string Nama database
     */
    public function getDatabaseName() {
        return $this->database;
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
 * Dapatkan koneksi database USER (default)
 * 
 * @return PDO Koneksi ke user_db
 */
function getDB() {
    return DatabaseMulti::getInstance('user_db')->getConnection();
}

/**
 * Dapatkan koneksi database ADMIN
 * 
 * @return PDO Koneksi ke admin_db
 */
function getAdminDB() {
    return DatabaseMulti::getInstance('admin_db')->getConnection();
}

/**
 * Dapatkan koneksi database berdasarkan nama
 * 
 * @param string $db_name Nama database ('user_db' atau 'admin_db')
 * @return PDO Koneksi database
 */
function getDBByName($db_name) {
    return DatabaseMulti::getInstance($db_name)->getConnection();
}

?>

