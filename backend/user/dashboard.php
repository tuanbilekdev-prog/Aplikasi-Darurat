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

// Periksa peran - harus 'user'
if (getUserRole() !== 'user') {
    header('Location: ../auth/login.php?error=' . urlencode('Akses ditolak'));
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
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM reports WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $total_reports = $stmt->fetch()['total'] ?? 0;
    
    // Ambil jumlah laporan yang menunggu
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM reports WHERE user_id = :user_id AND status = 'pending'");
    $stmt->execute(['user_id' => $user_id]);
    $pending_reports = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $recent_reports = [];
    $total_reports = 0;
    $pending_reports = 0;
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
                <div class="stat-card">
                    <div class="stat-icon stat-icon-primary">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $total_reports; ?></div>
                        <div class="stat-label">Total Laporan</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon stat-icon-warning">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $pending_reports; ?></div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                </div>
            </div>

            <!-- Main Cards Grid -->
            <div class="cards-grid">
                <!-- Card 1: Buat Laporan Darurat -->
                <div class="dashboard-card card-primary">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2 class="card-title">Buat Laporan Darurat</h2>
                    </div>
                    <p class="card-description">
                        Laporkan kejadian darurat yang Anda alami atau saksikan. 
                        Tim kami akan segera merespons laporan Anda.
                    </p>
                    <a href="create_report.php" class="card-button">
                        Buat Laporan
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

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
                                            'completed' => 'Selesai'
                                        ];
                                        echo $status_text[$report['status']] ?? ucfirst($report['status']);
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="history.php" class="card-link">Lihat Semua Riwayat →</a>
                </div>

                <!-- Card 3: Lokasi Saat Ini -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 5.02944 7.02944 1 12 1C16.9706 1 21 5.02944 21 10Z" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <h2 class="card-title">Lokasi Saat Ini</h2>
                    </div>
                    <p class="card-description">
                        Lokasi Anda akan otomatis dikirim saat membuat laporan darurat. 
                        Pastikan GPS aktif untuk akurasi yang lebih baik.
                    </p>
                    <div class="location-info">
                        <div class="location-status">
                            <span class="status-indicator"></span>
                            <span>GPS Siap</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Bantuan & Panduan -->
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

