<?php
/**
 * PROJECT ONE - DAFTAR LAPORAN
 * Halaman untuk menampilkan semua laporan darurat
 * 
 * FITUR:
 * - Filter berdasarkan status
 * - Filter berdasarkan kategori
 * - Pencarian laporan
 * - Pagination
 */

require_once __DIR__ . '/middleware/auth_admin.php';

// Wajibkan login admin
requireAdminLogin();

$admin_data = getAdminData();
$admin_name = $_SESSION['fullname'] ?? $admin_data['fullname'] ?? 'Admin';

// Parameter filter dan pencarian
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Koneksi database
$user_db = getDB();

// Query laporan dengan filter
$reports = [];
$total_reports = 0;

try {
    // Build query dengan filter
    $where_conditions = [];
    $params = [];
    
    if (!empty($status_filter)) {
        $where_conditions[] = "r.status = :status";
        $params['status'] = $status_filter;
    }
    
    if (!empty($category_filter)) {
        $where_conditions[] = "r.category = :category";
        $params['category'] = $category_filter;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(r.title LIKE :search OR r.description LIKE :search OR r.location LIKE :search OR u.fullname LIKE :search)";
        $params['search'] = '%' . $search . '%';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Query total untuk pagination
    $count_sql = "
        SELECT COUNT(*) as total
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        {$where_clause}
    ";
    $stmt = $user_db->prepare($count_sql);
    $stmt->execute($params);
    $total_reports = $stmt->fetch()['total'] ?? 0;
    
    // Query laporan dengan pagination
    $sql = "
        SELECT r.*, u.username, u.fullname as user_fullname, u.email as user_email, u.phone as user_phone
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        {$where_clause}
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $user_db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll();
    
    // Hitung total pages
    $total_pages = ceil($total_reports / $per_page);
    
} catch (PDOException $e) {
    error_log("Laporan list error: " . $e->getMessage());
    $error = "Terjadi kesalahan saat memuat data. Silakan coba lagi.";
}

// Kategori laporan
$categories = [
    'kecelakaan' => 'Kecelakaan',
    'kebakaran' => 'Kebakaran',
    'medis' => 'Medis',
    'kejahatan' => 'Kejahatan',
    'bencana' => 'Bencana',
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
    <title>Daftar Laporan - Admin Dashboard</title>
    
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
                    <h1 class="page-title">Daftar Laporan</h1>
                    <p class="page-subtitle">Kelola semua laporan darurat dari pengguna</p>
                </div>
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="page-main">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="search">Cari Laporan</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            class="form-input" 
                            placeholder="Cari berdasarkan judul, deskripsi, lokasi, atau nama pelapor..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo $status_filter === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo $category_filter === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="laporan_list.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Results Info -->
            <div class="results-info">
                <p>Menampilkan <strong><?php echo count($reports); ?></strong> dari <strong><?php echo $total_reports; ?></strong> laporan</p>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Pelapor</th>
                            <th>Lokasi</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="empty-state">
                                        <p>Tidak ada laporan ditemukan</p>
                                        <p class="empty-subtitle">Coba ubah filter atau kata kunci pencarian</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td>#<?php echo $report['id']; ?></td>
                                    <td>
                                        <div class="report-title-cell">
                                            <strong><?php echo htmlspecialchars($report['title']); ?></strong>
                                            <?php if ($report['urgent']): ?>
                                                <span class="badge badge-urgent">URGENT</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-category">
                                            <?php echo $categories[$report['category']] ?? ucfirst($report['category']); ?>
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
                                            <?php echo htmlspecialchars(mb_substr($report['location'], 0, 40)); ?>
                                            <?php if (mb_strlen($report['location']) > 40): ?>...<?php endif; ?>
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
                                            <?php echo $statuses[$report['status']] ?? ucfirst($report['status']); ?>
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

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <div class="pagination-info">
                        Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                    </div>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn">
                            Selanjutnya
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../partials/admin_footer.php'; ?>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/admin-dashboard.js"></script>
</body>
</html>

