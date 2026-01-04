<?php
/**
 * PROJECT ONE - DASHBOARD PENGGUNA
 * Dashboard untuk pengguna (masyarakat)
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

// Ambil data pengguna
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Pengguna';
$email = $_SESSION['email'] ?? '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

try {
    $db = getDB();
    
    // Ambil laporan terbaru pengguna (3 terakhir)
    $stmt = $db->prepare("
        SELECT id, title, status, created_at 
        FROM reports 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute(['user_id' => $user_id]);
    $recent_reports = $stmt->fetchAll();
    
    // Ambil jumlah total laporan
    $stmt = $db->prepare("SELECT COUNT(*) FROM reports WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $total_reports = (int)$stmt->fetchColumn();
    
    // Ambil jumlah laporan bulan ini
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM reports 
        WHERE user_id = :user_id 
        AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute(['user_id' => $user_id]);
    $reports_this_month = (int)$stmt->fetchColumn();
    
    // Ambil jumlah laporan berdasarkan status
    $statuses = ['pending', 'processing', 'dispatched', 'completed', 'cancelled'];
    $report_stats = [];
    foreach ($statuses as $status) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM reports WHERE user_id = :user_id AND status = :status");
        $stmt->execute(['user_id' => $user_id, 'status' => $status]);
        $report_stats[$status] = (int)$stmt->fetchColumn();
    }
    
    // Ambil laporan aktif (pending/processing) untuk card
    $stmt = $db->prepare("
        SELECT id, title, status, created_at, urgent
        FROM reports 
        WHERE user_id = :user_id 
        AND (status = 'pending' OR status = 'processing')
        ORDER BY urgent DESC, created_at DESC
        LIMIT 3
    ");
    $stmt->execute(['user_id' => $user_id]);
    $active_reports = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $recent_reports = [];
    $total_reports = 0;
    $report_stats = [
        'pending' => 0,
        'processing' => 0,
        'dispatched' => 0,
        'completed' => 0,
        'cancelled' => 0
    ];
    $active_reports = [];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Project One</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/user-dashboard.css">
</head>
<body>
    <?php include '../partials/user_navbar.php'; ?>

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

    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div>
                    <h1 class="header-title">Halo, <?php echo htmlspecialchars($username); ?></h1>
                    <p class="header-subtitle">Laporkan kejadian darurat dengan cepat dan aman</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container">
            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($total_reports); ?></div>
                        <div class="stat-label">Total Laporan</div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($report_stats['pending']); ?></div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($report_stats['processing']); ?></div>
                        <div class="stat-label">Diproses</div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: rgba(108, 117, 125, 0.1); border-left: 4px solid #6c757d;">
                    <div class="stat-icon" style="color: #6c757d;">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: #6c757d;"><?php echo number_format($report_stats['dispatched']); ?></div>
                        <div class="stat-label">Ditugaskan</div>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($report_stats['completed']); ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545;">
                    <div class="stat-icon" style="color: #dc3545;">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: #dc3545;"><?php echo number_format($report_stats['cancelled']); ?></div>
                        <div class="stat-label">Dibatalkan</div>
                    </div>
                </div>
            </div>

            <!-- Card: Laporan Aktif (Full Width) -->
            <div class="card-full-width-container" style="margin-bottom: 32px;">
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h2 class="card-title">Laporan Aktif</h2>
                    </div>
                    
                    <?php if (empty($active_reports)): ?>
                        <div class="empty-state">
                            <p>Tidak ada laporan aktif</p>
                            <p class="empty-subtitle">Semua laporan Anda sudah diproses</p>
                        </div>
                    <?php else: ?>
                        <div class="reports-list">
                            <?php foreach ($active_reports as $report): ?>
                                <a href="report_detail.php?id=<?php echo $report['id']; ?>" class="report-item" style="text-decoration: none; color: inherit;">
                                    <div class="report-info">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <h3 class="report-title"><?php echo htmlspecialchars($report['title'] ?? 'Laporan'); ?></h3>
                                            <?php if ($report['urgent']): ?>
                                                <span style="background: #E63946; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600;">DARURAT</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="report-date"><?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?></p>
                                    </div>
                                    <span class="status-badge status-<?php echo strtolower($report['status']); ?>">
                                        <?php 
                                        $status_text = [
                                            'pending' => 'Menunggu',
                                            'processing' => 'Diproses',
                                            'dispatched' => 'Ditugaskan',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        echo $status_text[$report['status']] ?? 'Tidak Diketahui';
                                        ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="history.php" class="card-link">Lihat Semua Laporan →</a>
                </div>
            </div>

            <!-- Main Cards Grid -->
            <div class="cards-grid">
                <!-- Card 2: Riwayat Laporan -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2 class="card-title">Riwayat Laporan</h2>
                    </div>
                    
                    <?php if (empty($recent_reports)): ?>
                        <div class="empty-state">
                            <p>Belum ada laporan</p>
                            <p class="empty-subtitle">Laporan Anda akan muncul di sini</p>
                        </div>
                    <?php else: ?>
                        <div class="reports-list">
                            <?php foreach ($recent_reports as $report): ?>
                                <div class="report-item">
                                    <div class="report-info">
                                        <h3 class="report-title"><?php echo htmlspecialchars($report['title'] ?? 'Laporan'); ?></h3>
                                        <p class="report-date"><?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?></p>
                                    </div>
                                    <span class="status-badge status-<?php echo strtolower($report['status']); ?>">
                                        <?php 
                                        $status_text = [
                                            'pending' => 'Menunggu',
                                            'processing' => 'Diproses',
                                            'dispatched' => 'Ditugaskan',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        echo $status_text[$report['status']] ?? 'Tidak Diketahui';
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="history.php" class="card-link">Lihat Semua Riwayat →</a>
                </div>

                <!-- Card 3: Bantuan & Panduan -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2 class="card-title">Bantuan & Panduan</h2>
                    </div>
                    <div class="help-content">
                        <div class="help-item">
                            <h3>Cara Melaporkan</h3>
                            <p>Klik tombol "Laporkan Darurat", isi form dengan jelas, dan kirim laporan Anda.</p>
                        </div>
                        <div class="help-item">
                            <h3>Nomor Darurat</h3>
                            <p class="emergency-number">119</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Emergency Button -->
    <a href="create_report.php" class="emergency-button" id="emergencyButton">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>LAPORKAN DARURAT</span>
    </a>

    <?php include '../partials/user_footer.php'; ?>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/user-dashboard.js"></script>
</body>
</html>

