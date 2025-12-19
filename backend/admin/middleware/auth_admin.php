<?php
/**
 * PROJECT ONE - MIDDLEWARE AUTHENTIKASI ADMIN
 * Validasi session dan akses admin
 * 
 * FUNGSI:
 * - Cek apakah admin sudah login
 * - Cek role admin (super_admin, admin, operator)
 * - Ambil data admin dari database admin_db
 * - Cegah akses jika tidak terautentikasi
 */

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database/connection.php';

/**
 * Cek apakah admin sudah login
 * 
 * @return bool True jika admin sudah login
 */
function isAdminLoggedIn() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['user_role']) && 
           in_array($_SESSION['user_role'], ['super_admin', 'admin', 'operator']) &&
           isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true;
}

/**
 * Dapatkan role admin saat ini
 * 
 * @return string|null Role admin atau null
 */
function getAdminRole() {
    if (isAdminLoggedIn()) {
        return $_SESSION['user_role'];
    }
    return null;
}

/**
 * Dapatkan ID admin saat ini
 * 
 * @return int|null ID admin atau null
 */
function getAdminId() {
    if (isAdminLoggedIn()) {
        return $_SESSION['user_id'] ?? null;
    }
    return null;
}

/**
 * Dapatkan ID instansi admin saat ini
 * 
 * @return int|null ID instansi atau null
 */
function getAdminInstansiId() {
    if (isAdminLoggedIn()) {
        return $_SESSION['admin_instansi_id'] ?? null;
    }
    return null;
}

/**
 * Wajibkan admin untuk login
 * Redirect ke halaman login jika belum login
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../auth/login.php?error=' . urlencode('Anda harus login sebagai admin'));
        exit();
    }
}

/**
 * Wajibkan role admin tertentu
 * 
 * @param array $allowed_roles Array role yang diizinkan
 */
function requireAdminRole($allowed_roles = ['super_admin', 'admin']) {
    requireAdminLogin();
    
    $current_role = getAdminRole();
    if (!in_array($current_role, $allowed_roles)) {
        header('Location: dashboard.php?error=' . urlencode('Akses ditolak. Role tidak memadai.'));
        exit();
    }
}

/**
 * Dapatkan data admin lengkap dari database
 * 
 * @return array|null Data admin atau null
 */
function getAdminData() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    try {
        $db = getDB(); // Single database: emergency_system
        $admin_id = getAdminId();
        
        $stmt = $db->prepare("
            SELECT a.*, i.nama as instansi_nama, i.kode as instansi_kode
            FROM admin a
            LEFT JOIN instansi i ON a.instansi_id = i.id
            WHERE a.id = :admin_id
            LIMIT 1
        ");
        $stmt->execute(['admin_id' => $admin_id]);
        $admin = $stmt->fetch();
        
        return $admin;
    } catch (PDOException $e) {
        error_log("Error getting admin data: " . $e->getMessage());
        return null;
    }
}

/**
 * Cek apakah admin memiliki akses ke instansi tertentu
 * 
 * @param int $instansi_id ID instansi yang dicek
 * @return bool True jika admin memiliki akses
 */
function hasInstansiAccess($instansi_id) {
    $admin_instansi_id = getAdminInstansiId();
    
    // Super admin bisa akses semua instansi
    if (getAdminRole() === 'super_admin') {
        return true;
    }
    
    // Admin biasa hanya bisa akses instansi sendiri
    return $admin_instansi_id == $instansi_id;
}

/**
 * Log aktivitas admin (opsional, untuk audit)
 * 
 * @param string $action Jenis aksi
 * @param string $description Deskripsi aksi
 */
function logAdminAction($action, $description = '') {
    if (!isAdminLoggedIn()) {
        return;
    }
    
    try {
        $db = getDB(); // Single database: emergency_system
        $admin_id = getAdminId();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $db->prepare("
            INSERT INTO log_admin (admin_id, action, description, ip_address, user_agent)
            VALUES (:admin_id, :action, :description, :ip_address, :user_agent)
        ");
        $stmt->execute([
            'admin_id' => $admin_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent
        ]);
    } catch (PDOException $e) {
        // Jangan tampilkan error jika log gagal (opsional feature)
        error_log("Failed to log admin action: " . $e->getMessage());
    }
}

?>

