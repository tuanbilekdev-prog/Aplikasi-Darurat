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
    'total_all' => 0,          // Total semua laporan
    'total_today' => 0,        // Laporan hari ini
    'urgent' => 0,             // Laporan darurat (urgent = 1)
    'pending' => 0,
    'processing' => 0,
    'dispatched' => 0,
    'completed' => 0
];

// Data untuk trend chart (7 hari terakhir)
$trend_data = [];

// Daftar laporan terbaru
$recent_reports = [];
// Daftar laporan darurat (urgent)
$urgent_reports = [];

try {
    // Query total semua laporan - menghitung SEMUA laporan tanpa filter apapun
    $stmt = $db->prepare("SELECT COUNT(*) FROM reports");
    $stmt->execute();
    $stats['total_all'] = (int)$stmt->fetchColumn();
    
    // Query statistik laporan hari ini
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM reports 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $stats['total_today'] = (int)$stmt->fetchColumn();
    
    // Query laporan darurat (urgent = 1)
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM reports 
        WHERE urgent = 1 AND status != 'completed' AND status != 'cancelled'
    ");
    $stmt->execute();
    $stats['urgent'] = (int)$stmt->fetchColumn();
    
    // Query statistik berdasarkan status - menggunakan fetchColumn untuk COUNT (lebih akurat)
    $statuses = ['pending', 'processing', 'dispatched', 'completed'];
    foreach ($statuses as $status) {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM reports 
            WHERE status = :status
        ");
        $stmt->execute(['status' => $status]);
        $stats[$status] = (int)$stmt->fetchColumn();
    }
    
    // Query trend 7 hari terakhir untuk line chart
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM reports 
            WHERE DATE(created_at) = :date
        ");
        $stmt->execute(['date' => $date]);
        $count = (int)$stmt->fetchColumn();
        
        $trend_data[] = [
            'date' => $date,
            'label' => date('d M', strtotime($date)),
            'total' => $count
        ];
    }
    
    // Query statistik berdasarkan kategori
    $category_stats = [];
    $categories = ['kecelakaan', 'kebakaran', 'medis', 'kejahatan', 'bencana', 'lainnya'];
    $category_labels = [
        'kecelakaan' => 'Kecelakaan',
        'kebakaran' => 'Kebakaran',
        'medis' => 'Darurat Medis',
        'kejahatan' => 'Kejahatan',
        'bencana' => 'Bencana Alam',
        'lainnya' => 'Lainnya'
    ];
    
    foreach ($categories as $category) {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM reports 
            WHERE category = :category
        ");
        $stmt->execute(['category' => $category]);
        $count = (int)$stmt->fetchColumn();
        if ($count > 0) {
            $category_stats[] = [
                'label' => $category_labels[$category],
                'value' => $count,
                'category' => $category
            ];
        }
    }
    
    // Query laporan aktif - hanya menampilkan status: pending, processing, dispatched (tanpa limit)
    $stmt = $db->prepare("
        SELECT r.*, u.username, u.fullname as user_fullname, u.email as user_email
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.status IN ('pending', 'processing', 'dispatched')
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $recent_reports = $stmt->fetchAll();
    
    // Query laporan darurat (urgent) yang belum selesai - tampilkan semua (tanpa limit karena prioritas tinggi)
    $stmt = $db->prepare("
        SELECT r.*, u.username, u.fullname as user_fullname, u.email as user_email
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.urgent = 1 AND r.status != 'completed' AND r.status != 'cancelled'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $urgent_reports = $stmt->fetchAll();
    
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
                        <div class="stat-number"><?php echo number_format($stats['total_all']); ?></div>
                        <div class="stat-label">Total Laporan</div>
                    </div>
                </div>
                
                <div class="stat-card" style="background: linear-gradient(135deg, #E63946 0%, #C1121F 100%); color: white;">
                    <div class="stat-icon" style="color: white;">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 8V12M12 12V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: white;"><?php echo number_format($stats['urgent']); ?></div>
                        <div class="stat-label" style="color: rgba(255, 255, 255, 0.9);">Laporan Darurat</div>
                    </div>
                </div>
                
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
                
                <div class="stat-card" style="background: rgba(108, 117, 125, 0.1); border-left: 4px solid #6c757d;">
                    <div class="stat-icon" style="color: #6c757d;">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: #6c757d;"><?php echo number_format($stats['dispatched']); ?></div>
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
                        <div class="stat-number"><?php echo number_format($stats['completed']); ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
            </div>

            <!-- Chart Kategori dan Trend -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Grafik Statistik</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 32px;">
                    <!-- Pie Chart Kategori -->
                    <?php if (!empty($category_stats)): ?>
                    <div class="chart-container" style="background: var(--white); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-md);">
                        <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text); margin-bottom: 16px;">Statistik Berdasarkan Kategori</h3>
                        <canvas id="categoryChart" style="max-height: 350px;"></canvas>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Line Chart Trend 7 Hari -->
                    <?php if (!empty($trend_data)): ?>
                    <div class="chart-container" style="background: var(--white); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-md);">
                        <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text); margin-bottom: 16px;">Trend 7 Hari Terakhir</h3>
                        <canvas id="trendChart" style="max-height: 350px;"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Laporan Darurat (Urgent) -->
            <?php if (!empty($urgent_reports)): ?>
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title" style="color: #E63946;">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px; display: inline-block; vertical-align: middle; margin-right: 8px;">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Laporan Darurat (Prioritas Tinggi)
                    </h2>
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
                            <?php foreach ($urgent_reports as $report): ?>
                                <tr style="background: rgba(230, 57, 70, 0.05); border-left: 4px solid #E63946;">
                                    <td>
                                        <strong style="color: #E63946;">#<?php echo $report['id']; ?></strong>
                                        <span style="background: #E63946; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; margin-left: 8px; font-weight: 600;">DARURAT</span>
                                    </td>
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
                                            echo $status_text[$report['status']] ?? 'Tidak Diketahui';
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
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Laporan Aktif -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Laporan Aktif</h2>
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
                                                echo $status_text[$report['status']] ?? 'Tidak Diketahui';
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

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- JavaScript -->
    <script src="../../frontend/assets/js/admin-dashboard.js"></script>
    
    <script>
        // Data kategori dari PHP
        const categoryData = <?php echo json_encode($category_stats); ?>;
        
        // Pie Chart untuk Kategori
        if (categoryData && categoryData.length > 0) {
            const ctx = document.getElementById('categoryChart');
            if (ctx) {
                // Warna untuk setiap kategori
                const categoryColors = {
                    'kecelakaan': '#F59E0B',      // Kuning/Orange
                    'kebakaran': '#EF4444',       // Merah
                    'medis': '#10B981',           // Hijau
                    'kejahatan': '#6366F1',       // Indigo
                    'bencana': '#8B5CF6',         // Ungu
                    'lainnya': '#6B7280'          // Abu-abu
                };
                
                const labels = categoryData.map(item => item.label);
                const data = categoryData.map(item => item.value);
                const backgroundColors = categoryData.map(item => categoryColors[item.category] || '#6B7280');
                
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Laporan',
                            data: data,
                            backgroundColor: backgroundColors,
                            borderColor: '#FFFFFF',
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        family: "'Inter', sans-serif"
                                    },
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.parsed + ' laporan';
                                        return label;
                                    }
                                },
                                padding: 12,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1000
                        }
                    }
                });
            }
        }
        
        // Data trend dari PHP
        const trendData = <?php echo json_encode($trend_data); ?>;
        
        // Line Chart untuk Trend 7 Hari Terakhir
        if (trendData && trendData.length > 0) {
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                const labels = trendData.map(item => item.label);
                const data = trendData.map(item => item.total);
                
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Laporan',
                            data: data,
                            borderColor: '#4DA3FF',
                            backgroundColor: 'rgba(77, 163, 255, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4DA3FF',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointHoverBackgroundColor: '#4DA3FF',
                            pointHoverBorderColor: '#FFFFFF',
                            pointHoverBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Jumlah Laporan: ' + context.parsed.y;
                                    }
                                },
                                padding: 12,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>

