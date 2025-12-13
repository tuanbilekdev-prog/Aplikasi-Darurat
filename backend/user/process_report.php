<?php
/**
 * PROJECT ONE - PROCESS REPORT
 * Handle report submission
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/connection.php';

// Check authentication
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Check role - must be 'user'
if (getUserRole() !== 'user') {
    header('Location: ../auth/login.php?error=' . urlencode('Akses ditolak'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_report.php');
    exit();
}

// Get and sanitize form data
$title = sanitizeInput($_POST['title'] ?? '');
$category = sanitizeInput($_POST['category'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$location = sanitizeInput($_POST['location'] ?? '');
$urgent = isset($_POST['urgent']) ? 1 : 0;

// Validation
$errors = [];

if (empty($title)) {
    $errors[] = 'Judul laporan wajib diisi';
}

if (empty($category)) {
    $errors[] = 'Kategori wajib dipilih';
}

if (empty($description)) {
    $errors[] = 'Deskripsi wajib diisi';
} elseif (strlen($description) < 10) {
    $errors[] = 'Deskripsi minimal 10 karakter';
}

if (empty($location)) {
    $errors[] = 'Lokasi wajib diisi';
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $error_msg = implode(', ', $errors);
    header('Location: create_report.php?error=' . urlencode($error_msg));
    exit();
}

try {
    $db = getDB();
    
    // Insert report into database
    $stmt = $db->prepare("
        INSERT INTO reports (user_id, title, category, description, location, urgent, status, created_at)
        VALUES (:user_id, :title, :category, :description, :location, :urgent, 'pending', NOW())
    ");
    
    $stmt->execute([
        'user_id' => $user_id,
        'title' => $title,
        'category' => $category,
        'description' => $description,
        'location' => $location,
        'urgent' => $urgent
    ]);
    
    // Success - redirect to dashboard
    header('Location: dashboard.php?success=' . urlencode('Laporan berhasil dikirim. Tim kami akan segera merespons.'));
    exit();
    
} catch (PDOException $e) {
    error_log("Report submission error: " . $e->getMessage());
    header('Location: create_report.php?error=' . urlencode('Terjadi kesalahan. Silakan coba lagi.'));
    exit();
}

?>

