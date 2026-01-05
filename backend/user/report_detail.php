<?php
/**
 * PROJECT ONE - DETAIL LAPORAN USER
 * Halaman untuk melihat detail lengkap laporan dan respon admin
 */

session_start();
// Load Docker config jika di Docker environment, jika tidak load config.php biasa
if (file_exists(__DIR__ . '/../config.docker.php') && getenv('DB_HOST') === 'db') {
    require_once __DIR__ . '/../config.docker.php';
} else {
    require_once __DIR__ . '/../config.php';
}
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

// Ambil ID laporan dari URL
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$report_id) {
    header('Location: history.php?error=' . urlencode('ID laporan tidak valid'));
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Koneksi database
$db = getDB();

// Ambil data laporan (hanya laporan milik user yang login)
$report = null;
$report_media = [];

try {
    $stmt = $db->prepare("
        SELECT * 
        FROM reports 
        WHERE id = :report_id AND user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute([
        'report_id' => $report_id,
        'user_id' => $user_id
    ]);
    $report = $stmt->fetch();
    
    if (!$report) {
        header('Location: history.php?error=' . urlencode('Laporan tidak ditemukan atau Anda tidak memiliki akses'));
        exit();
    }
    
    // Ambil media/foto laporan jika ada
    $stmt = $db->prepare("
        SELECT * FROM report_media 
        WHERE report_id = :report_id 
        ORDER BY created_at ASC
    ");
    $stmt->execute(['report_id' => $report_id]);
    $report_media = $stmt->fetchAll();
    
    // Hitung waktu response admin (dari created_at ke updated_at jika status bukan pending)
    $response_time = null;
    $response_time_text = null;
    if ($report && $report['status'] != 'pending' && $report['updated_at']) {
        $created_time = strtotime($report['created_at']);
        $updated_time = strtotime($report['updated_at']);
        $diff_seconds = $updated_time - $created_time;
        
        if ($diff_seconds > 0) {
            $response_time = $diff_seconds;
            $hours = floor($diff_seconds / 3600);
            $minutes = floor(($diff_seconds % 3600) / 60);
            
            if ($hours > 0) {
                $response_time_text = $hours . ' jam';
                if ($minutes > 0) {
                    $response_time_text .= ' ' . $minutes . ' menit';
                }
            } else {
                $response_time_text = $minutes . ' menit';
            }
        }
    }
    
} catch (PDOException $e) {
    error_log("Report detail error: " . $e->getMessage());
    $error = "Terjadi kesalahan saat memuat data. Silakan coba lagi.";
    $response_time = null;
    $response_time_text = null;
}

// Mapping kategori dan status
$categories = [
    'kecelakaan' => 'Kecelakaan',
    'kebakaran' => 'Kebakaran',
    'medis' => 'Darurat Medis',
    'kejahatan' => 'Kejahatan',
    'bencana' => 'Bencana Alam',
    'lainnya' => 'Lainnya'
];

$status_text = [
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
    <title>Detail Laporan - Project One</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/user-dashboard.css">
    <style>
        .detail-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-md);
            margin-bottom: 24px;
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border);
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .card-body {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .info-row {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .info-row label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 1rem;
            color: var(--text);
            font-weight: 500;
        }
        
        .response-section {
            background: linear-gradient(135deg, rgba(77, 163, 255, 0.05) 0%, rgba(77, 163, 255, 0.02) 100%);
            border: 2px solid rgba(77, 163, 255, 0.2);
            border-radius: var(--radius);
            padding: 20px;
            margin-top: 24px;
        }
        
        .response-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .response-section h3 svg {
            width: 20px;
            height: 20px;
            color: var(--accent);
        }
        
        .response-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .response-item:last-child {
            margin-bottom: 0;
        }
        
        .response-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-light);
        }
        
        .response-value {
            font-size: 1rem;
            color: var(--text);
            line-height: 1.6;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        
        .media-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--border);
            background: var(--background);
        }
        
        .media-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .media-info {
            padding: 12px;
            background: var(--white);
        }
        
        .media-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 4px;
            word-break: break-word;
        }
        
        .media-size {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        
        .response-notes {
            background: var(--white);
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
        }
        
        .no-response {
            text-align: center;
            padding: 32px;
            color: var(--text-light);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: var(--white);
            color: var(--text);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            margin-bottom: 24px;
        }
        
        .btn-back:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .btn-back svg {
            width: 18px;
            height: 18px;
        }
        
        /* Timeline Styles */
        .timeline-container {
            position: relative;
            padding-left: 32px;
        }
        
        .timeline-line {
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 24px;
            padding-bottom: 0;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-item:last-child .timeline-line {
            display: none;
        }
        
        .timeline-dot {
            position: absolute;
            left: -32px;
            top: 4px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--white);
            border: 3px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        
        .timeline-item.active .timeline-dot {
            background: var(--accent);
            border-color: var(--accent);
        }
        
        .timeline-item.completed .timeline-dot {
            background: #22C55E;
            border-color: #22C55E;
        }
        
        .timeline-dot svg {
            width: 12px;
            height: 12px;
            color: var(--white);
        }
        
        .timeline-content {
            background: var(--white);
            padding: 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        
        .timeline-item.active .timeline-content {
            background: rgba(77, 163, 255, 0.05);
            border-color: var(--accent);
        }
        
        .timeline-item.completed .timeline-content {
            background: rgba(34, 197, 94, 0.05);
            border-color: #22C55E;
        }
        
        .timeline-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 4px;
        }
        
        .timeline-description {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 8px;
        }
        
        .timeline-date {
            font-size: 0.8125rem;
            color: var(--text-light);
        }
        
        .response-time-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(34, 197, 94, 0.1);
            color: #22C55E;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .response-time-badge svg {
            width: 14px;
            height: 14px;
        }
    </style>
</head>
<body>
    <?php include '../partials/user_navbar.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <a href="history.php" class="btn-back">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali ke Riwayat
            </a>
            
            <div class="page-header">
                <h1 class="page-title">Detail Laporan</h1>
                <p class="page-subtitle">Informasi lengkap laporan Anda</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 24px; padding: 16px; background: rgba(220, 53, 69, 0.1); color: #dc3545; border-radius: 8px; border-left: 4px solid #dc3545;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 24px; padding: 16px; background: rgba(34, 197, 94, 0.1); color: #22C55E; border-radius: 8px; border-left: 4px solid #22C55E;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($report): ?>
                <!-- Informasi Laporan -->
                <div class="detail-card">
                    <div class="card-header">
                        <h2 class="card-title">Informasi Laporan</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <label>Judul</label>
                            <p class="info-value"><?php echo htmlspecialchars($report['title']); ?></p>
                        </div>
                        
                        <div class="info-row">
                            <label>Status</label>
                            <span class="status-badge status-<?php echo strtolower($report['status']); ?>">
                                <?php echo $status_text[$report['status']] ?? 'Tidak Diketahui'; ?>
                            </span>
                        </div>
                        
                        <div class="info-row">
                            <label>Kategori</label>
                            <p class="info-value"><?php echo htmlspecialchars($categories[$report['category']] ?? 'Lainnya'); ?></p>
                        </div>
                        
                        <div class="info-row">
                            <label>Deskripsi</label>
                            <p class="info-value" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                        </div>
                        
                        <div class="info-row">
                            <label>Lokasi</label>
                            <p class="info-value"><?php echo htmlspecialchars($report['location']); ?></p>
                        </div>
                        
                        <div class="info-row">
                            <label>Tanggal Dibuat</label>
                            <p class="info-value"><?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?></p>
                        </div>
                        
                        <?php if ($report['updated_at'] && $report['updated_at'] != $report['created_at']): ?>
                            <div class="info-row">
                                <label>Terakhir Diupdate</label>
                                <p class="info-value"><?php echo date('d M Y, H:i', strtotime($report['updated_at'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline Laporan -->
                <div class="detail-card">
                    <div class="card-header">
                        <h2 class="card-title">Timeline Laporan</h2>
                    </div>
                    <div class="card-body">
                        <div class="timeline-container">
                            <div class="timeline-line"></div>
                            
                            <!-- Step 1: Dibuat (Pending) -->
                            <div class="timeline-item <?php echo $report['status'] == 'pending' ? 'active' : 'completed'; ?>">
                                <div class="timeline-dot">
                                    <?php if ($report['status'] == 'pending'): ?>
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Laporan Dibuat</div>
                                    <div class="timeline-description">Laporan Anda telah dibuat dan menunggu penanganan</div>
                                    <div class="timeline-date"><?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?></div>
                                </div>
                            </div>
                            
                            <!-- Step 2: Diproses -->
                            <?php if ($report['status'] != 'pending'): ?>
                                <div class="timeline-item <?php echo $report['status'] == 'processing' ? 'active' : ($report['status'] == 'dispatched' || $report['status'] == 'completed' ? 'completed' : ''); ?>">
                                    <div class="timeline-dot">
                                        <?php if ($report['status'] == 'processing'): ?>
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Sedang Diproses</div>
                                        <div class="timeline-description">Laporan sedang ditinjau dan diproses oleh admin</div>
                                        <?php if ($report['updated_at'] && $report['status'] != 'pending'): ?>
                                            <div class="timeline-date"><?php echo date('d M Y, H:i', strtotime($report['updated_at'])); ?></div>
                                        <?php endif; ?>
                                        <?php if ($response_time_text): ?>
                                            <div class="response-time-badge">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                </svg>
                                                Direspons dalam <?php echo $response_time_text; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Step 3: Ditugaskan -->
                            <?php if ($report['status'] == 'dispatched' || $report['status'] == 'completed'): ?>
                                <div class="timeline-item <?php echo $report['status'] == 'dispatched' ? 'active' : 'completed'; ?>">
                                    <div class="timeline-dot">
                                        <?php if ($report['status'] == 'dispatched'): ?>
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Ditugaskan ke Instansi</div>
                                        <div class="timeline-description">
                                            <?php if ($report['dispatched_to']): ?>
                                                Laporan ditugaskan ke: <strong><?php echo htmlspecialchars($report['dispatched_to']); ?></strong>
                                            <?php else: ?>
                                                Laporan telah ditugaskan ke instansi terkait
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($report['dispatched_at']): ?>
                                            <div class="timeline-date"><?php echo date('d M Y, H:i', strtotime($report['dispatched_at'])); ?></div>
                                        <?php elseif ($report['updated_at']): ?>
                                            <div class="timeline-date"><?php echo date('d M Y, H:i', strtotime($report['updated_at'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Step 4: Selesai -->
                            <?php if ($report['status'] == 'completed'): ?>
                                <div class="timeline-item completed">
                                    <div class="timeline-dot">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Selesai</div>
                                        <div class="timeline-description">Laporan telah selesai ditangani</div>
                                        <?php if ($report['completed_at']): ?>
                                            <div class="timeline-date"><?php echo date('d M Y, H:i', strtotime($report['completed_at'])); ?></div>
                                        <?php elseif ($report['updated_at']): ?>
                                            <div class="timeline-date"><?php echo date('d M Y, H:i', strtotime($report['updated_at'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Media/Foto Laporan -->
                <?php if (!empty($report_media)): ?>
                    <div class="detail-card">
                        <div class="card-header">
                            <h2 class="card-title">Foto Kejadian</h2>
                        </div>
                        <div class="card-body">
                            <div class="media-grid">
                                <?php foreach ($report_media as $media): ?>
                                    <div class="media-item">
                                        <img src="../../<?php echo htmlspecialchars($media['file_path']); ?>" alt="Foto laporan" class="media-image" onerror="this.style.display='none'">
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

                <!-- Respon Admin -->
                <?php if ($report['admin_notes'] || $report['dispatched_to'] || $report['dispatched_at'] || $report['completed_at']): ?>
                    <div class="detail-card response-section">
                        <h3>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Respon dari Admin
                        </h3>
                        
                        <div class="card-body">
                            <?php if ($report['admin_notes']): ?>
                                <div class="response-item">
                                    <label class="response-label">Catatan Penanganan</label>
                                    <div class="response-notes">
                                        <p class="response-value"><?php echo nl2br(htmlspecialchars($report['admin_notes'])); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($report['dispatched_to']): ?>
                                <div class="response-item">
                                    <label class="response-label">Ditugaskan ke Instansi</label>
                                    <p class="response-value"><?php echo htmlspecialchars($report['dispatched_to']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($report['dispatched_at']): ?>
                                <div class="response-item">
                                    <label class="response-label">Waktu Ditugaskan</label>
                                    <p class="response-value"><?php echo date('d M Y, H:i', strtotime($report['dispatched_at'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($report['completed_at']): ?>
                                <div class="response-item">
                                    <label class="response-label">Waktu Selesai</label>
                                    <p class="response-value"><?php echo date('d M Y, H:i', strtotime($report['completed_at'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="detail-card">
                        <div class="no-response">
                            <p>Belum ada respon dari admin</p>
                            <p style="font-size: 0.875rem; margin-top: 8px;">Admin akan memberikan update segera setelah laporan Anda diproses.</p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../partials/user_footer.php'; ?>
</body>
</html>

