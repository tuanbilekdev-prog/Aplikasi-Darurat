<?php
/**
 * PROJECT ONE - API GENERATE AI SUGGESTION
 * Endpoint API untuk generate AI response suggestion
 */

require_once __DIR__ . '/middleware/auth_admin.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../helpers/ai_helper.php';

// Wajibkan login admin
requireAdminLogin();

// Set JSON header
header('Content-Type: application/json');

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
    
    // Generate AI suggestion
    if (OPENAI_API_ENABLED) {
        $result = generateAISuggestion(
            $report['title'],
            $report['description'],
            $report['category'],
            $report['location'],
            $report['status']
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

?>
