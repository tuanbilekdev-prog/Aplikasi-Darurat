<?php
/**
 * PROJECT ONE - API GENERATE AI SUGGESTION
 * Endpoint API untuk generate AI response suggestion
 */

// Start output buffering untuk mencegah output sebelum JSON
ob_start();

// Disable error display untuk API (hanya log ke error_log)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/middleware/auth_admin.php';
// Load Docker config jika di Docker environment, jika tidak load config.php biasa
if (file_exists(__DIR__ . '/../config.docker.php') && getenv('DB_HOST') === 'db') {
    require_once __DIR__ . '/../config.docker.php';
} else {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../helpers/ai_helper.php';

// Wajibkan login admin
requireAdminLogin();

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

// Ambil report_id dari POST
$report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;

if (!$report_id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Report ID tidak valid'
    ]);
    exit;
}

// Ambil status yang dipilih dari dropdown (jika ada), jika tidak gunakan status dari database
$selected_status = isset($_POST['status']) ? $_POST['status'] : null;

try {
    $db = getDB();
    
    // Ambil data laporan
    $stmt = $db->prepare("
        SELECT title, description, category, location, status
        FROM reports
        WHERE id = :report_id
        LIMIT 1
    ");
    $stmt->execute(['report_id' => $report_id]);
    $report = $stmt->fetch();
    
    if (!$report) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Laporan tidak ditemukan'
        ]);
        exit;
    }
    
    // Gunakan status yang dipilih dari dropdown jika ada, jika tidak gunakan status dari database
    $status_to_use = $selected_status ? $selected_status : $report['status'];
    
    // Generate AI suggestion dengan status yang dipilih
    if (OPENAI_API_ENABLED) {
        $result = generateAISuggestion(
            $report['title'],
            $report['description'],
            $report['category'],
            $report['location'],
            $status_to_use
        );
    } else {
        // Fallback ke rule-based suggestion
        $result = generateFallbackSuggestion(
            $report['category'],
            $report['description']
        );
    }
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'suggestion' => $result['suggestion'],
            'fallback' => isset($result['fallback']) ? $result['fallback'] : false
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Gagal generate suggestion'
        ]);
    }
    
} catch (Exception $e) {
    error_log("API Generate Suggestion Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan server: ' . $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
