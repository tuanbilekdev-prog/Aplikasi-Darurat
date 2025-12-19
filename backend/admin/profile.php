<?php
/**
 * PROJECT ONE - PROFIL ADMIN
 * Halaman profil admin
 */

require_once __DIR__ . '/middleware/auth_admin.php';

// Wajibkan login admin
requireAdminLogin();

// Ambil data admin
$admin_data = getAdminData();
$admin_id = getAdminId();

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Project One</title>
    
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
                    <h1 class="page-title">Profil Admin</h1>
                    <p class="page-subtitle">Informasi akun dan instansi Anda</p>
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
            <div class="detail-grid">
                <!-- Informasi Admin -->
                <div class="detail-main">
                    <div class="detail-card">
                        <div class="card-header">
                            <h2 class="card-title">Informasi Admin</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($admin_data): ?>
                                <div class="info-row">
                                    <label>Nama Lengkap</label>
                                    <p class="info-value"><?php echo htmlspecialchars($admin_data['fullname']); ?></p>
                                </div>
                                
                                <div class="info-row">
                                    <label>Username</label>
                                    <p class="info-value"><?php echo htmlspecialchars($admin_data['username']); ?></p>
                                </div>
                                
                                <div class="info-row">
                                    <label>Email</label>
                                    <p class="info-value"><?php echo htmlspecialchars($admin_data['email']); ?></p>
                                </div>
                                
                                <div class="info-row">
                                    <label>Role</label>
                                    <p class="info-value">
                                        <span class="badge badge-category">
                                            <?php 
                                            $roles = [
                                                'super_admin' => 'Super Admin',
                                                'admin' => 'Admin',
                                                'operator' => 'Operator'
                                            ];
                                            echo $roles[$admin_data['role']] ?? ucfirst($admin_data['role']);
                                            ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="info-row">
                                    <label>Status</label>
                                    <p class="info-value">
                                        <span class="status-badge status-<?php echo strtolower($admin_data['status']); ?>">
                                            <?php 
                                            $statuses = [
                                                'active' => 'Aktif',
                                                'inactive' => 'Tidak Aktif',
                                                'suspended' => 'Ditangguhkan'
                                            ];
                                            echo $statuses[$admin_data['status']] ?? ucfirst($admin_data['status']);
                                            ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <?php if ($admin_data['last_login']): ?>
                                    <div class="info-row">
                                        <label>Login Terakhir</label>
                                        <p class="info-value"><?php echo date('d M Y, H:i', strtotime($admin_data['last_login'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="info-row">
                                    <label>Bergabung</label>
                                    <p class="info-value"><?php echo date('d M Y', strtotime($admin_data['created_at'])); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <p>Data admin tidak ditemukan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Informasi Instansi -->
                <div class="detail-sidebar">
                    <?php if ($admin_data && isset($admin_data['instansi_nama'])): ?>
                        <div class="detail-card">
                            <div class="card-header">
                                <h2 class="card-title">Instansi</h2>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <label>Nama Instansi</label>
                                    <p class="info-value"><?php echo htmlspecialchars($admin_data['instansi_nama']); ?></p>
                                </div>
                                
                                <?php if ($admin_data['instansi_kode']): ?>
                                    <div class="info-row">
                                        <label>Kode Instansi</label>
                                        <p class="info-value"><?php echo htmlspecialchars($admin_data['instansi_kode']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../partials/admin_footer.php'; ?>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/admin-dashboard.js"></script>
</body>
</html>

