<?php
/**
 * PROJECT ONE - API GENERATE AI DESCRIPTION
 * Endpoint API untuk generate deskripsi laporan menggunakan AI
 * - Jika ada foto: generate berdasarkan gambar (vision API)
 * - Jika tidak ada foto: generate berdasarkan judul dan kategori
 */

session_start();
// Load Docker config jika di Docker environment, jika tidak load config.php biasa
if (file_exists(__DIR__ . '/../config.docker.php') && getenv('DB_HOST') === 'db') {
    require_once __DIR__ . '/../config.docker.php';
} else {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../helpers/ai_helper.php';

// Wajibkan login user
if (!isLoggedIn() || getUserRole() !== 'user') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
}

// Set JSON header
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Clear output buffer dan set JSON header
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

// Ambil data dari POST
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$photo = isset($_FILES['photo']) ? $_FILES['photo'] : null;

if (!$title || !$category) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Judul dan kategori harus diisi'
    ]);
    exit;
}

try {
    // Generate description
    if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
        // Generate berdasarkan gambar (vision API)
        $result = generateAIDescriptionFromImage($title, $category, $photo);
    } else {
        // Generate berdasarkan judul dan kategori saja
        $result = generateAIDescriptionFromText($title, $category);
    }
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'description' => $result['description'],
            'from_image' => isset($result['from_image']) ? $result['from_image'] : false
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Gagal generate deskripsi'
        ]);
    }
    
} catch (Exception $e) {
    error_log("API Generate Description Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan server: ' . $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();

