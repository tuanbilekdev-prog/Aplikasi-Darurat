<?php
/**
 * PROJECT ONE - DASHBOARD ADMIN
 * Dashboard untuk admin instansi
 * 
 * FITUR:
 * - Statistik laporan (total, pending, processing, completed)
 * - Daftar laporan terbaru
 * - Quick actions
 */

require_once __DIR__ . '/middleware/auth_admin.php';

// Wajibkan login admin
requireAdminLogin();

// Ambil data admin
$admin_data = getAdminData();
$admin_id = getAdminId();
$admin_instansi_id = getAdminInstansiId();
$admin_role = getAdminRole();
$admin_name = $_SESSION['fullname'] ?? $admin_data['fullname'] ?? 'Admin';

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Koneksi database (single database: emergency_system)
$db = getDB();

// Statistik laporan
$stats = [
    'total_today' => 0,
    'pending' => 0,
    'processing' => 0,
    'completed' => 0,
    'dispatched' => 0
];

// Daftar laporan terbaru
$recent_reports = [];

try {
    // Query statistik laporan hari ini
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM reports 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $stats['total_today'] = $stmt->fetch()['total'] ?? 0;
    
    // Query statistik berdasarkan status
    $statuses = ['pending', 'processing', 'dispatched', 'completed'];
    foreach ($statuses as $status) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM reports 
            WHERE status = :status
        ");
        $stmt->execute(['status' => $status]);
        $stats[$status] = $stmt->fetch()['total'] ?? 0;
    }
    
    // Query laporan terbaru (10 terakhir)
    $stmt = $db->prepare("
        SELECT r.*, u.username, u.fullname as user_fullname, u.email as user_email
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_reports = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard admin error: " . $e->getMessage());
    $error = "Terjadi kesalahan saat memuat data. Silakan coba lagi.";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Project One</title>
    
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

    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div>
                    <h1 class="header-title">Dashboard Admin</h1>
                    <p class="header-subtitle">
                        Selamat datang, <?php echo htmlspecialchars($admin_name); ?> 
                        <?php if ($admin_data && isset($admin_data['instansi_nama'])): ?>
                            - <?php echo htmlspecialchars($admin_data['instansi_nama']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="header-actions">
                    <a href="map_dashboard.php" class="btn btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 5.02944 7.02944 1 12 1C16.9706 1 21 5.02944 21 10Z" stroke="currentColor" stroke-width="2"/>
                            <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Lihat Peta
                    </a>
                    <a href="laporan_list.php" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Lihat Semua Laporan
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container">
            <!-- Statistik Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['total_today']); ?></div>
                        <div class="stat-label">Laporan Hari Ini</div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($stats['pending']); ?></div>
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
                        <div class="stat-number"><?php echo number_format($stats['processing']); ?></div>
                        <div class="stat-label">Diproses</div>
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
                        <div class="stat-number"><?php echo number_format($stats['completed']); ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
            </div>

            <!-- Laporan Terbaru -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Laporan Terbaru</h2>
                    <a href="laporan_list.php" class="section-link">Lihat Semua →</a>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jenis</th>
                                <th>Pelapor</th>
                                <th>Lokasi</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_reports)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-state">
                                            <p>Belum ada laporan</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_reports as $report): ?>
                                    <tr>
                                        <td>#<?php echo $report['id']; ?></td>
                                        <td>
                                            <span class="badge badge-category">
                                                <?php echo ucfirst($report['category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <strong><?php echo htmlspecialchars($report['user_fullname'] ?? $report['username']); ?></strong>
                                                <small><?php echo htmlspecialchars($report['user_email'] ?? ''); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="location-info">
                                                <?php echo htmlspecialchars(mb_substr($report['location'], 0, 30)); ?>
                                                <?php if (mb_strlen($report['location']) > 30): ?>...<?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="time-info">
                                                <?php echo date('d M Y', strtotime($report['created_at'])); ?>
                                                <small><?php echo date('H:i', strtotime($report['created_at'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($report['status']); ?>">
                                                <?php 
                                                $status_text = [
                                                    'pending' => 'Menunggu',
                                                    'processing' => 'Diproses',
                                                    'dispatched' => 'Ditugaskan',
                                                    'completed' => 'Selesai',
                                                    'cancelled' => 'Dibatalkan'
                                                ];
                                                echo $status_text[$report['status']] ?? ucfirst($report['status']);
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="laporan_detail.php?id=<?php echo $report['id']; ?>" class="btn-action btn-view">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                                </svg>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include '../partials/admin_footer.php'; ?>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/admin-dashboard.js"></script>
</body>
</html>

