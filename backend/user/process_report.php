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

// Periksa peran - harus 'user' (bukan admin)
$user_role = getUserRole();
if ($user_role !== 'user') {
    // Jika user adalah admin, redirect ke admin dashboard
    if (in_array($user_role, ['super_admin', 'admin', 'operator'])) {
        header('Location: ../admin/dashboard.php?error=' . urlencode('Akses ditolak. Halaman ini hanya untuk user biasa.'));
        exit();
    }
    // Jika role tidak valid, clear session dan redirect ke login
    session_destroy();
    header('Location: ../auth/login.php?error=' . urlencode('Akses ditolak. Silakan login ulang.'));
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
    
    // Ambil ID laporan yang baru dibuat
    $report_id = $db->lastInsertId();
    
    // Handle upload foto jika ada
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        $upload_dir = __DIR__ . '/../../uploads/reports/';
        
        // Pastikan folder uploads/reports ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $max_files = 5;
        
        $files = $_FILES['photos'];
        $file_count = count($files['name']);
        
        // Validasi jumlah file
        if ($file_count > $max_files) {
            // Jika terlalu banyak file, hapus report yang sudah dibuat
            $stmt = $db->prepare("DELETE FROM reports WHERE id = :report_id");
            $stmt->execute(['report_id' => $report_id]);
            
            header('Location: create_report.php?error=' . urlencode('Maksimal ' . $max_files . ' foto yang dapat diupload'));
            exit();
        }
        
        // Proses setiap file
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $original_name = $files['name'][$i];
                $file_size = $files['size'][$i];
                $file_type = $files['type'][$i];
                
                // Validasi tipe file
                if (!in_array($file_type, $allowed_types)) {
                    continue; // Skip file yang tidak valid
                }
                
                // Validasi ukuran file
                if ($file_size > $max_size) {
                    continue; // Skip file yang terlalu besar
                }
                
                // Validasi bahwa file benar-benar gambar
                $image_info = getimagesize($tmp_name);
                if ($image_info === false) {
                    continue; // Skip file yang bukan gambar
                }
                
                // Generate nama file unik (timestamp + random + ekstensi)
                $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
                $new_filename = time() . '_' . uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Pindahkan file ke folder uploads
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    // Simpan data media ke database
                    $relative_path = 'uploads/reports/' . $new_filename;
                    
                    $stmt_media = $db->prepare("
                        INSERT INTO report_media (report_id, file_path, file_name, file_type, file_size, created_at)
                        VALUES (:report_id, :file_path, :file_name, :file_type, :file_size, NOW())
                    ");
                    
                    $stmt_media->execute([
                        'report_id' => $report_id,
                        'file_path' => $relative_path,
                        'file_name' => $original_name,
                        'file_type' => $file_type,
                        'file_size' => $file_size
                    ]);
                }
            }
        }
    }
    
    // Berhasil - arahkan ke dashboard
    header('Location: dashboard.php?success=' . urlencode('Laporan berhasil dikirim. Tim kami akan segera merespons.'));
    exit();
    
} catch (PDOException $e) {
    error_log("Report submission error: " . $e->getMessage());
    header('Location: create_report.php?error=' . urlencode('Terjadi kesalahan. Silakan coba lagi.'));
    exit();
}

?>

