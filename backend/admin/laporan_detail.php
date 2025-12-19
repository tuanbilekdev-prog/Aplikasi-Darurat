<?php
/**
 * PROJECT ONE - DETAIL LAPORAN
 * Halaman untuk melihat detail lengkap laporan darurat
 * 
 * FITUR:
 * - Tampilkan semua informasi laporan
 * - Data pelapor
 * - Media/foto laporan (jika ada)
 * - Koordinat lokasi
 * - Aksi admin (update status, tambah catatan)
 */

require_once __DIR__ . '/middleware/auth_admin.php';

// Wajibkan login admin
requireAdminLogin();

$admin_data = getAdminData();
$admin_id = getAdminId();

// Ambil ID laporan dari URL
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$report_id) {
    header('Location: laporan_list.php?error=' . urlencode('ID laporan tidak valid'));
    exit();
}

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Koneksi database (single database: emergency_system)
$db = getDB();

// Ambil data laporan
$report = null;
$report_media = [];

try {
    // Query detail laporan dengan data user
    $stmt = $db->prepare("
        SELECT r.*, u.username, u.fullname as user_fullname, u.email as user_email, u.phone as user_phone
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.id = :report_id
        LIMIT 1
    ");
    $stmt->execute(['report_id' => $report_id]);
    $report = $stmt->fetch();
    
    if (!$report) {
        header('Location: laporan_list.php?error=' . urlencode('Laporan tidak ditemukan'));
        exit();
    }
    
    // Query media laporan (jika ada)
    $stmt = $db->prepare("
        SELECT * FROM report_media 
        WHERE report_id = :report_id 
        ORDER BY created_at ASC
    ");
    $stmt->execute(['report_id' => $report_id]);
    $report_media = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Laporan detail error: " . $e->getMessage());
    $error = "Terjadi kesalahan saat memuat data. Silakan coba lagi.";
}

// Kategori dan status mapping
$categories = [
    'kecelakaan' => 'Kecelakaan',
    'kebakaran' => 'Kebakaran',
    'medis' => 'Medis',
    'kejahatan' => 'Kejahatan',
    'bencana' => 'Bencana',
    'lainnya' => 'Lainnya'
];

$statuses = [
    'pending' => 'Menunggu',
    'processing' => 'Diproses',
    'dispatched' => 'Ditugaskan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan #<?php echo $report_id; ?> - Admin Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/admin-dashboard.css">
</head>
<body>
    <?php include '../partials/admin_navbar.php'; ?>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-success">
                <svg class="alert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-error">
                <svg class="alert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <div class="header-content">
                <div>
                    <h1 class="page-title">Detail Laporan #<?php echo $report_id; ?></h1>
                    <p class="page-subtitle">Informasi lengkap laporan darurat</p>
                </div>
                <div class="header-actions">
                    <a href="laporan_list.php" class="btn btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="page-main">
        <div class="container">
            <div class="detail-grid">
                <!-- Left Column: Informasi Laporan -->
                <div class="detail-main">
                    <!-- Status Card -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h2 class="card-title">Status Laporan</h2>
                        </div>
                        <div class="card-body">
                            <div class="status-display">
                                <span class="status-badge status-<?php echo strtolower($report['status']); ?> large">
                                    <?php echo $statuses[$report['status']] ?? ucfirst($report['status']); ?>
                                </span>
                                <?php if ($report['urgent']): ?>
                                    <span class="badge badge-urgent large">URGENT</span>
                                <?php endif; ?>
                            </div>
                            <div class="status-info">
                                <p><strong>Dibuat:</strong> <?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?></p>
                                <?php if ($report['updated_at'] && $report['updated_at'] != $report['created_at']): ?>
                                    <p><strong>Diupdate:</strong> <?php echo date('d M Y, H:i', strtotime($report['updated_at'])); ?></p>
                                <?php endif; ?>
                                <?php if ($report['dispatched_at']): ?>
                                    <p><strong>Ditugaskan:</strong> <?php echo date('d M Y, H:i', strtotime($report['dispatched_at'])); ?></p>
                                <?php endif; ?>
                                <?php if ($report['completed_at']): ?>
                                    <p><strong>Selesai:</strong> <?php echo date('d M Y, H:i', strtotime($report['completed_at'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Laporan -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h2 class="card-title">Informasi Laporan</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <label>Judul Laporan</label>
                                <p class="info-value"><?php echo htmlspecialchars($report['title']); ?></p>
                            </div>
                            
                            <div class="info-row">
                                <label>Kategori</label>
                                <p class="info-value">
                                    <span class="badge badge-category">
                                        <?php echo $categories[$report['category']] ?? ucfirst($report['category']); ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="info-row">
                                <label>Deskripsi</label>
                                <p class="info-value"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                            </div>
                            
                            <div class="info-row">
                                <label>Lokasi</label>
                                <p class="info-value"><?php echo htmlspecialchars($report['location']); ?></p>
                                <?php if ($report['latitude'] && $report['longitude']): ?>
                                    <p class="info-subvalue">
                                        Koordinat: <?php echo $report['latitude']; ?>, <?php echo $report['longitude']; ?>
                                        <a href="https://www.google.com/maps?q=<?php echo $report['latitude']; ?>,<?php echo $report['longitude']; ?>" target="_blank" class="map-link">
                                            Buka di Google Maps
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Media Laporan -->
                    <?php if (!empty($report_media)): ?>
                        <div class="detail-card">
                            <div class="card-header">
                                <h2 class="card-title">Media Laporan</h2>
                            </div>
                            <div class="card-body">
                                <div class="media-grid">
                                    <?php foreach ($report_media as $media): ?>
                                        <div class="media-item">
                                            <img src="../../<?php echo htmlspecialchars($media['file_path']); ?>" alt="Media laporan" class="media-image" onerror="this.style.display='none'">
                                            <div class="media-info">
                                                <p class="media-name"><?php echo htmlspecialchars($media['file_name'] ?? 'Gambar'); ?></p>
                                                <?php if ($media['file_size']): ?>
                                                    <p class="media-size"><?php echo number_format($media['file_size'] / 1024, 2); ?> KB</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Catatan Admin -->
                    <?php if ($report['admin_notes']): ?>
                        <div class="detail-card">
                            <div class="card-header">
                                <h2 class="card-title">Catatan Admin</h2>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br(htmlspecialchars($report['admin_notes'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Data Pelapor & Aksi -->
                <div class="detail-sidebar">
                    <!-- Data Pelapor -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h2 class="card-title">Data Pelapor</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <label>Nama</label>
                                <p class="info-value"><?php echo htmlspecialchars($report['user_fullname'] ?? $report['username']); ?></p>
                            </div>
                            
                            <div class="info-row">
                                <label>Username</label>
                                <p class="info-value"><?php echo htmlspecialchars($report['username']); ?></p>
                            </div>
                            
                            <div class="info-row">
                                <label>Email</label>
                                <p class="info-value"><?php echo htmlspecialchars($report['user_email'] ?? '-'); ?></p>
                            </div>
                            
                            <?php if ($report['user_phone']): ?>
                                <div class="info-row">
                                    <label>Telepon</label>
                                    <p class="info-value"><?php echo htmlspecialchars($report['user_phone']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Aksi Admin -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h2 class="card-title">Aksi Admin</h2>
                        </div>
                        <div class="card-body">
                            <form action="proses_laporan.php" method="POST" class="action-form">
                                <input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
                                
                                <div class="form-group">
                                    <label for="status">Ubah Status</label>
                                    <select id="status" name="status" class="form-select" required>
                                        <option value="pending" <?php echo $report['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                        <option value="processing" <?php echo $report['status'] === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="dispatched" <?php echo $report['status'] === 'dispatched' ? 'selected' : ''; ?>>Ditugaskan</option>
                                        <option value="completed" <?php echo $report['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="cancelled" <?php echo $report['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="admin_notes">Catatan Penanganan</label>
                                    <textarea 
                                        id="admin_notes" 
                                        name="admin_notes" 
                                        class="form-textarea" 
                                        rows="4"
                                        placeholder="Tambahkan catatan penanganan laporan ini..."
                                    ><?php echo htmlspecialchars($report['admin_notes'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="dispatched_to">Ditugaskan ke Instansi</label>
                                    <input 
                                        type="text" 
                                        id="dispatched_to" 
                                        name="dispatched_to" 
                                        class="form-input" 
                                        placeholder="Nama instansi yang ditugaskan..."
                                        value="<?php echo htmlspecialchars($report['dispatched_to'] ?? ''); ?>"
                                    >
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-block">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../partials/admin_footer.php'; ?>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/admin-dashboard.js"></script>
</body>
</html>

