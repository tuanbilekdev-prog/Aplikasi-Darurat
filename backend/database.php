<?php
/**
 * PROJECT ONE - KONEKSI DATABASE
 * Backend: Penanganan koneksi database
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection = null;
    
    /**
     * Dapatkan instance database (pola Singleton)
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
            // Di production, catat error alih-alih menampilkan
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please contact administrator.");
        }
    }
    
    /**
     * Dapatkan koneksi database
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

// Fungsi bantuan untuk mendapatkan koneksi database
function getDB() {
    return Database::getInstance()->getConnection();
}

?>

