<?php
/**
 * PROJECT ONE - HALAMAN RIWAYAT
 * Halaman riwayat laporan pengguna
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

try {
    $db = getDB();
    
    // Ambil semua laporan pengguna
    $stmt = $db->prepare("
        SELECT id, title, category, status, description, location, created_at, updated_at
        FROM reports 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $reports = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("History error: " . $e->getMessage());
    $reports = [];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Laporan - Project One</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/user-dashboard.css">
</head>
<body>
    <?php include '../partials/user_navbar.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Riwayat Laporan</h1>
                <p class="page-subtitle">Semua laporan darurat yang telah Anda buat</p>
            </div>

            <?php if (empty($reports)): ?>
                <div class="empty-state-large">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h2>Belum Ada Laporan</h2>
                    <p>Anda belum membuat laporan darurat. Klik tombol di bawah untuk membuat laporan pertama Anda.</p>
                    <a href="create_report.php" class="btn btn-primary">Buat Laporan Pertama</a>
                </div>
            <?php else: ?>
                <div class="reports-grid">
                    <?php foreach ($reports as $report): ?>
                        <div class="report-card">
                            <div class="report-card-header">
                                <h3 class="report-card-title"><?php echo htmlspecialchars($report['title']); ?></h3>
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
                            <div class="report-card-body">
                                <div class="report-meta">
                                    <span class="meta-item">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <?php echo htmlspecialchars($report['category'] ?? 'Lainnya'); ?>
                                    </span>
                                    <span class="meta-item">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <?php echo date('d M Y, H:i', strtotime($report['created_at'])); ?>
                                    </span>
                                </div>
                                <p class="report-description"><?php echo htmlspecialchars(substr($report['description'], 0, 150)); ?><?php echo strlen($report['description']) > 150 ? '...' : ''; ?></p>
                                <?php if ($report['location']): ?>
                                    <div class="report-location">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 5.02944 7.02944 1 12 1C16.9706 1 21 5.02944 21 10Z" stroke="currentColor" stroke-width="2"/>
                                            <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                        <span><?php echo htmlspecialchars($report['location']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../partials/user_footer.php'; ?>

    <script src="../../frontend/assets/js/user-dashboard.js"></script>
</body>
</html>

