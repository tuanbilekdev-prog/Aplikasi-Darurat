<?php
/**
 * PROJECT ONE - PROSES LAPORAN
 * Menangani pengiriman laporan
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/connection.php';

// Periksa autentikasi
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Periksa peran - harus 'user'
if (getUserRole() !== 'user') {
    header('Location: ../auth/login.php?error=' . urlencode('Akses ditolak'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Periksa apakah formulir dikirim
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_report.php');
    exit();
}

// Ambil dan bersihkan data formulir
$title = sanitizeInput($_POST['title'] ?? '');
$category = sanitizeInput($_POST['category'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$location = sanitizeInput($_POST['location'] ?? '');
$latitude = isset($_POST['latitude']) && !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) && !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$urgent = isset($_POST['urgent']) ? 1 : 0;

// Validasi
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

// Validasi koordinat (opsional, tapi disarankan)
if ($latitude === null || $longitude === null) {
    $errors[] = 'Silakan pilih lokasi di peta atau gunakan tombol "Gunakan Lokasi Saya"';
}

// Jika ada error, arahkan kembali dengan pesan error
if (!empty($errors)) {
    $error_msg = implode(', ', $errors);
    header('Location: create_report.php?error=' . urlencode($error_msg));
    exit();
}

try {
    $db = getDB();
    
    // Masukkan laporan ke database (termasuk latitude dan longitude)
    $stmt = $db->prepare("
        INSERT INTO reports (user_id, title, category, description, location, latitude, longitude, urgent, status, created_at)
        VALUES (:user_id, :title, :category, :description, :location, :latitude, :longitude, :urgent, 'pending', NOW())
    ");
    
    $stmt->execute([
        'user_id' => $user_id,
        'title' => $title,
        'category' => $category,
        'description' => $description,
        'location' => $location,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'urgent' => $urgent
    ]);
    
    // Berhasil - arahkan ke dashboard
    header('Location: dashboard.php?success=' . urlencode('Laporan berhasil dikirim. Tim kami akan segera merespons.'));
    exit();
    
} catch (PDOException $e) {
    error_log("Report submission error: " . $e->getMessage());
    header('Location: create_report.php?error=' . urlencode('Terjadi kesalahan. Silakan coba lagi.'));
    exit();
}

?>

