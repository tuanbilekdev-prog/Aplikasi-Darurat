<?php
/**
 * PROJECT ONE - PROSES LAPORAN
 * Handler untuk update status dan catatan laporan
 * 
 * FUNGSI:
 * - Update status laporan
 * - Tambah/edit catatan admin
 * - Set instansi yang ditugaskan
 * - Log aktivitas admin
 */

require_once __DIR__ . '/middleware/auth_admin.php';

// Wajibkan login admin
requireAdminLogin();

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: laporan_list.php?error=' . urlencode('Metode tidak diizinkan'));
    exit();
}

// Ambil data dari form
$report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
$status = sanitizeInput($_POST['status'] ?? '');
$admin_notes = sanitizeInput($_POST['admin_notes'] ?? '');
$dispatched_to = sanitizeInput($_POST['dispatched_to'] ?? '');

// Validasi
if (!$report_id) {
    header('Location: laporan_list.php?error=' . urlencode('ID laporan tidak valid'));
    exit();
}

if (empty($status)) {
    header('Location: laporan_detail.php?id=' . $report_id . '&error=' . urlencode('Status harus diisi'));
    exit();
}

// Validasi status
$valid_statuses = ['pending', 'processing', 'dispatched', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    header('Location: laporan_detail.php?id=' . $report_id . '&error=' . urlencode('Status tidak valid'));
    exit();
}

// Koneksi database (single database: emergency_system)
$db = getDB();
$admin_id = getAdminId();

try {
    // Cek apakah laporan ada
    $stmt = $db->prepare("SELECT id, status FROM reports WHERE id = :report_id LIMIT 1");
    $stmt->execute(['report_id' => $report_id]);
    $report = $stmt->fetch();
    
    if (!$report) {
        header('Location: laporan_list.php?error=' . urlencode('Laporan tidak ditemukan'));
        exit();
    }
    
    // Prepare update query
    $update_fields = [];
    $update_params = ['report_id' => $report_id];
    
    // Update status
    $update_fields[] = "status = :status";
    $update_params['status'] = $status;
    
    // Update catatan admin
    $update_fields[] = "admin_notes = :admin_notes";
    $update_params['admin_notes'] = $admin_notes;
    
    // Update instansi yang ditugaskan
    if (!empty($dispatched_to)) {
        $update_fields[] = "dispatched_to = :dispatched_to";
        $update_params['dispatched_to'] = $dispatched_to;
    }
    
    // Set waktu dispatched jika status berubah ke dispatched
    if ($status === 'dispatched' && $report['status'] !== 'dispatched') {
        $update_fields[] = "dispatched_at = NOW()";
    }
    
    // Reset dispatched_at jika status mundur dari dispatched/completed ke tahap sebelumnya
    if (($status === 'pending' || $status === 'processing') && ($report['status'] === 'dispatched' || $report['status'] === 'completed')) {
        $update_fields[] = "dispatched_at = NULL";
    }
    
    // Set waktu completed jika status berubah ke completed
    if ($status === 'completed' && $report['status'] !== 'completed') {
        $update_fields[] = "completed_at = NOW()";
    }
    
    // Reset completed_at jika status mundur dari completed ke tahap sebelumnya
    if ($status !== 'completed' && $report['status'] === 'completed') {
        $update_fields[] = "completed_at = NULL";
    }
    
    // Update timestamp
    $update_fields[] = "updated_at = NOW()";
    
    // Execute update
    $sql = "UPDATE reports SET " . implode(', ', $update_fields) . " WHERE id = :report_id";
    $stmt = $db->prepare($sql);
    $stmt->execute($update_params);
    
    // Log aktivitas admin
    $action_description = "Update laporan #{$report_id}: Status diubah dari '{$report['status']}' ke '{$status}'";
    logAdminAction('update_report', $action_description);
    
    // Redirect dengan pesan sukses
    header('Location: laporan_detail.php?id=' . $report_id . '&success=' . urlencode('Laporan berhasil diperbarui'));
    exit();
    
} catch (PDOException $e) {
    error_log("Proses laporan error: " . $e->getMessage());
    header('Location: laporan_detail.php?id=' . $report_id . '&error=' . urlencode('Terjadi kesalahan saat memperbarui laporan. Silakan coba lagi.'));
    exit();
}

