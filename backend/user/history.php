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

// Parameter filter dan pencarian
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

try {
    $db = getDB();
    
    // Build query dengan filter
    $where_conditions = ['user_id = :user_id'];
    $params = ['user_id' => $user_id];
    
    if (!empty($status_filter)) {
        $where_conditions[] = "status = :status";
        $params['status'] = $status_filter;
    }
    
    if (!empty($category_filter)) {
        $where_conditions[] = "category = :category";
        $params['category'] = $category_filter;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(title LIKE :search OR description LIKE :search OR location LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Ambil semua laporan pengguna dengan filter
    $stmt = $db->prepare("
        SELECT id, title, category, status, description, location, 
               admin_notes, dispatched_to, dispatched_at, completed_at,
               created_at, updated_at
        FROM reports 
        {$where_clause}
        ORDER BY created_at DESC
    ");
    $stmt->execute($params);
    $reports = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("History error: " . $e->getMessage());
    $reports = [];
}

// Kategori laporan
$categories = [
    'kecelakaan' => 'Kecelakaan',
    'kebakaran' => 'Kebakaran',
    'medis' => 'Darurat Medis',
    'kejahatan' => 'Kejahatan',
    'bencana' => 'Bencana Alam',
    'lainnya' => 'Lainnya'
];

// Status laporan
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

            <!-- Filter Section -->
            <?php if (!empty($reports) || !empty($status_filter) || !empty($category_filter) || !empty($search)): ?>
                <div class="filter-section" style="background: var(--white); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-md); margin-bottom: 24px;">
                    <form method="GET" class="filter-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
                        <div class="filter-group" style="display: flex; flex-direction: column; gap: 8px;">
                            <label for="search" style="font-size: 0.875rem; font-weight: 600; color: var(--text);">Cari Laporan</label>
                            <input 
                                type="text" 
                                id="search" 
                                name="search" 
                                class="form-input" 
                                placeholder="Cari berdasarkan judul, deskripsi, atau lokasi..."
                                value="<?php echo htmlspecialchars($search); ?>"
                                style="padding: 10px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9375rem; width: 100%;"
                            >
                        </div>
                        
                        <div class="filter-group" style="display: flex; flex-direction: column; gap: 8px;">
                            <label for="status" style="font-size: 0.875rem; font-weight: 600; color: var(--text);">Status</label>
                            <select id="status" name="status" class="form-select" style="padding: 10px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9375rem; width: 100%; background: var(--white);">
                                <option value="">Semua Status</option>
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $status_filter === $key ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group" style="display: flex; flex-direction: column; gap: 8px;">
                            <label for="category" style="font-size: 0.875rem; font-weight: 600; color: var(--text);">Kategori</label>
                            <select id="category" name="category" class="form-select" style="padding: 10px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9375rem; width: 100%; background: var(--white);">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $category_filter === $key ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-actions" style="display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; background: var(--primary); color: var(--white); border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9375rem;">
                                Filter
                            </button>
                            <a href="history.php" class="btn btn-secondary" style="padding: 10px 20px; background: var(--background); color: var(--text); border: 1px solid var(--border); border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9375rem;">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Results Info -->
            <?php if (!empty($reports) || !empty($status_filter) || !empty($category_filter) || !empty($search)): ?>
                <div style="margin-bottom: 16px; color: var(--text-light); font-size: 0.875rem;">
                    Menampilkan <strong><?php echo count($reports); ?></strong> laporan
                    <?php if (!empty($status_filter) || !empty($category_filter) || !empty($search)): ?>
                        <a href="history.php" style="color: var(--accent); margin-left: 8px; text-decoration: none;">Tampilkan semua</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

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
                        <a href="report_detail.php?id=<?php echo $report['id']; ?>" class="report-card" style="text-decoration: none; color: inherit; display: block;">
                            <div class="report-card-header">
                                <h3 class="report-card-title"><?php echo htmlspecialchars($report['title']); ?></h3>
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
                            <div class="report-card-body">
                                <div class="report-meta">
                                    <span class="meta-item">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <?php 
                                        $category_text = [
                                            'kecelakaan' => 'Kecelakaan',
                                            'kebakaran' => 'Kebakaran',
                                            'medis' => 'Darurat Medis',
                                            'kejahatan' => 'Kejahatan',
                                            'bencana' => 'Bencana Alam',
                                            'lainnya' => 'Lainnya'
                                        ];
                                        echo htmlspecialchars($category_text[$report['category']] ?? 'Lainnya'); 
                                        ?>
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
                                
                                <?php if ($report['admin_notes'] || $report['dispatched_to']): ?>
                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
                                        <span style="font-size: 0.8125rem; color: var(--accent); font-weight: 500; display: flex; align-items: center; gap: 6px;">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;">
                                                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Ada respon dari admin
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../partials/user_footer.php'; ?>

    <script src="../../frontend/assets/js/user-dashboard.js"></script>
</body>
</html>

