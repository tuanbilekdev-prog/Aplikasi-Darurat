<?php
/**
 * PROJECT ONE - DASHBOARD ADMIN - GOOGLE MAPS
 * Dashboard admin dengan Google Maps untuk menampilkan semua laporan
 * 
 * FITUR:
 * - Menampilkan Google Map dengan semua marker laporan
 * - Info window saat marker diklik (jenis, deskripsi, waktu)
 * - Filter berdasarkan status/kategori
 */

require_once __DIR__ . '/middleware/auth_admin.php';

// Wajibkan login admin
requireAdminLogin();

// Ambil data admin
$admin_data = getAdminData();
$admin_id = getAdminId();
$admin_name = $_SESSION['fullname'] ?? $admin_data['fullname'] ?? 'Admin';

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Koneksi database (single database: emergency_system)
$db = getDB();

// Ambil semua laporan dengan koordinat
$reports = [];

try {
    $stmt = $db->prepare("
        SELECT 
            r.id,
            r.title,
            r.category,
            r.description,
            r.location,
            r.latitude,
            r.longitude,
            r.status,
            r.urgent,
            r.created_at,
            u.username,
            u.fullname as user_fullname,
            u.email as user_email
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.latitude IS NOT NULL AND r.longitude IS NOT NULL
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $reports = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Map dashboard error: " . $e->getMessage());
    $error = "Terjadi kesalahan saat memuat data. Silakan coba lagi.";
}

// Konversi data ke JSON untuk JavaScript
$reports_json = json_encode($reports, JSON_HEX_APOS | JSON_HEX_QUOT);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Laporan - Dashboard Admin - Project One</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../../frontend/assets/css/maps.css">
    
    <!-- Leaflet.js - OpenStreetMap (GRATIS, tidak perlu API key) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                    <h1 class="header-title">Peta Laporan Darurat</h1>
                    <p class="header-subtitle">
                        Selamat datang, <?php echo htmlspecialchars($admin_name); ?> - 
                        Total <?php echo count($reports); ?> laporan dengan koordinat
                    </p>
                </div>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                    <a href="laporan_list.php" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Lihat Daftar Laporan
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container">
            <!-- Filter Controls -->
            <div class="map-filters" style="margin-bottom: 20px;">
                <div class="filter-group" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <div>
                        <label for="filterStatus" style="margin-right: 8px; font-weight: 500;">Filter Status:</label>
                        <select id="filterStatus" class="form-input" style="width: auto; display: inline-block;">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="processing">Diproses</option>
                            <option value="dispatched">Ditugaskan</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="filterCategory" style="margin-right: 8px; font-weight: 500;">Filter Kategori:</label>
                        <select id="filterCategory" class="form-input" style="width: auto; display: inline-block;">
                            <option value="">Semua Kategori</option>
                            <option value="kecelakaan">Kecelakaan</option>
                            <option value="kebakaran">Kebakaran</option>
                            <option value="medis">Darurat Medis</option>
                            <option value="kejahatan">Kejahatan</option>
                            <option value="bencana">Bencana Alam</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- CATATAN: Fitur "Cari Instansi Darurat Terdekat" DIHAPUS -->
                    <!-- Fitur ini memerlukan Google Places API yang berbayar -->
                    <!-- Untuk prototype, data instansi dapat ditambahkan manual ke database -->
                </div>
            </div>

            <!-- OpenStreetMap Container (Leaflet.js) -->
            <div id="map" class="admin-map-container"></div>

            <!-- Legend -->
            <div class="map-legend">
                <h4>Legenda</h4>
                <div class="map-legend-item">
                    <div class="map-legend-icon" style="background-color: #ffc107;"></div>
                    <span>Menunggu</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-legend-icon" style="background-color: #17a2b8;"></div>
                    <span>Diproses</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-legend-icon" style="background-color: #6c757d;"></div>
                    <span>Ditugaskan</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-legend-icon" style="background-color: #28a745;"></div>
                    <span>Selesai</span>
                </div>
                <div class="map-legend-item">
                    <div class="map-legend-icon" style="background-color: #dc3545;"></div>
                    <span>Dibatalkan</span>
                </div>
            </div>
        </div>
    </main>

    <?php include '../partials/admin_footer.php'; ?>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/admin-dashboard.js"></script>
    <script>
        // Data laporan dari PHP
        const reportsData = <?php echo $reports_json; ?>;
        
        // Variabel global untuk Leaflet Map
        let map;
        let markers = [];
        const defaultCenter = [ -6.2088, 106.8456 ]; // Jakarta, Indonesia [lat, lng]
        
        // CATATAN: Aplikasi menggunakan OpenStreetMap dengan Leaflet.js (GRATIS)
        // Tidak memerlukan API key, cocok untuk prototype dan production
        // Fitur "Cari Instansi Darurat Terdekat" dihapus karena memerlukan Google Places API yang berbayar

        // Inisialisasi Leaflet Map (OpenStreetMap - GRATIS, tidak perlu API key)
        document.addEventListener('DOMContentLoaded', function() {
            // Buat peta
            map = L.map('map').setView(defaultCenter, 11);

            // Tambahkan tile layer dari OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Tampilkan semua marker
            displayMarkers(reportsData);

            // Event listener untuk filter
            document.getElementById('filterStatus').addEventListener('change', filterMarkers);
            document.getElementById('filterCategory').addEventListener('change', filterMarkers);
        });
        
        // Fungsi untuk mendapatkan warna marker berdasarkan status
        function getMarkerColor(status) {
            const colors = {
                'pending': '#ffc107',      // Kuning
                'processing': '#17a2b8',   // Biru
                'dispatched': '#6c757d',   // Abu-abu
                'completed': '#28a745',     // Hijau
                'cancelled': '#dc3545'      // Merah
            };
            return colors[status] || '#6c757d';
        }

        // Fungsi untuk membuat custom icon Leaflet berdasarkan status
        function createMarkerIcon(status, urgent) {
            const color = getMarkerColor(status);
            const iconSize = urgent ? 40 : 32;
            
            // Buat custom icon menggunakan HTML
            return L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${color}; width: ${iconSize}px; height: ${iconSize}px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [iconSize, iconSize],
                iconAnchor: [iconSize / 2, iconSize / 2]
            });
        }

        // Fungsi untuk menampilkan marker
        function displayMarkers(reports) {
            // Hapus marker lama
            clearMarkers();

            if (reports.length === 0) {
                return;
            }

            // Buat bounds untuk fit semua marker
            const bounds = L.latLngBounds([]);

            reports.forEach(function(report) {
                const lat = parseFloat(report.latitude);
                const lng = parseFloat(report.longitude);

                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }

                const position = [lat, lng];
                bounds.extend(position);

                // Buat marker dengan custom icon
                const marker = L.marker(position, {
                    icon: createMarkerIcon(report.status, report.urgent == 1),
                    title: report.title
                }).addTo(map);

                // Buat popup content
                const popupContent = createInfoWindowContent(report);
                
                // Bind popup ke marker
                marker.bindPopup(popupContent);

                // Event listener untuk klik marker
                marker.on('click', function() {
                    // Leaflet popup sudah otomatis menutup popup lain saat membuka yang baru
                });

                markers.push(marker);
            });

            // Fit bounds untuk menampilkan semua marker
            if (markers.length > 0) {
                map.fitBounds(bounds);
                
                // Jika hanya ada 1 marker, set zoom level
                if (markers.length === 1) {
                    map.setZoom(15);
                }
            }
        }

        // Fungsi untuk membuat konten info window
        function createInfoWindowContent(report) {
            const statusText = {
                'pending': 'Menunggu',
                'processing': 'Diproses',
                'dispatched': 'Ditugaskan',
                'completed': 'Selesai',
                'cancelled': 'Dibatalkan'
            };

            const categoryText = {
                'kecelakaan': 'Kecelakaan',
                'kebakaran': 'Kebakaran',
                'medis': 'Darurat Medis',
                'kejahatan': 'Kejahatan',
                'bencana': 'Bencana Alam',
                'lainnya': 'Lainnya'
            };

            const date = new Date(report.created_at);
            const formattedDate = date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            return `
                <div class="info-window-content" style="min-width: 250px;">
                    <div class="info-window-title" style="font-weight: bold; font-size: 16px; margin-bottom: 8px;">${escapeHtml(report.title)}</div>
                    <div class="info-window-detail" style="margin-bottom: 4px;">
                        <strong>Jenis Laporan:</strong> ${categoryText[report.category] || report.category}
                    </div>
                    <div class="info-window-detail" style="margin-bottom: 4px;">
                        <strong>Status:</strong> ${statusText[report.status] || report.status}
                    </div>
                    <div class="info-window-detail" style="margin-bottom: 4px;">
                        <strong>Pelapor:</strong> ${escapeHtml(report.user_fullname || report.username || 'Tidak diketahui')}
                    </div>
                    <div class="info-window-detail" style="margin-bottom: 4px;">
                        <strong>Lokasi:</strong> ${escapeHtml(report.location)}
                    </div>
                    <div class="info-window-detail" style="margin-top: 8px; margin-bottom: 8px;">
                        ${escapeHtml(report.description.substring(0, 100))}${report.description.length > 100 ? '...' : ''}
                    </div>
                    <div class="info-window-time" style="font-size: 12px; color: #666; margin-bottom: 8px;">
                        <strong>Waktu Laporan:</strong> ${formattedDate}
                    </div>
                    <div style="margin-top: 12px;">
                        <a href="laporan_detail.php?id=${report.id}" 
                           style="display: inline-block; padding: 6px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">
                            Lihat Detail →
                        </a>
                    </div>
                </div>
            `;
        }

        // Fungsi untuk escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Fungsi untuk menghapus semua marker
        function clearMarkers() {
            markers.forEach(function(marker) {
                map.removeLayer(marker);
            });
            markers = [];
        }

        // Fungsi untuk filter marker
        function filterMarkers() {
            const statusFilter = document.getElementById('filterStatus').value;
            const categoryFilter = document.getElementById('filterCategory').value;

            const filteredReports = reportsData.filter(function(report) {
                const statusMatch = !statusFilter || report.status === statusFilter;
                const categoryMatch = !categoryFilter || report.category === categoryFilter;
                return statusMatch && categoryMatch;
            });

            displayMarkers(filteredReports);
        }
    </script>
</body>
</html>

