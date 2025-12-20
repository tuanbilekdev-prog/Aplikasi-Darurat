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
    
    <!-- Google Maps API -->
    <!-- CATATAN: libraries=places DIPERLUKAN untuk fitur "Cari Instansi Darurat Terdekat" -->
    <!-- Namun, Places API hanya dipanggil saat admin menekan tombol manual, bukan otomatis -->
    <!-- Ini menghemat credit Places API secara signifikan -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : 'YOUR_GOOGLE_MAPS_API_KEY'; ?>&libraries=places&callback=initMap" async defer></script>
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

                    <!-- Tombol Cari Instansi Darurat Terdekat -->
                    <!-- CATATAN: Tombol ini memicu request ke Places API -->
                    <!-- Untuk menghemat credit, request hanya dilakukan saat tombol diklik -->
                    <!-- Data hasil akan di-cache di database untuk penggunaan selanjutnya -->
                    <div>
                        <button type="button" id="findInstansiBtn" class="btn btn-primary" style="margin-left: 16px;">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; margin-right: 6px;">
                                <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 5.02944 7.02944 1 12 1C16.9706 1 21 5.02944 21 10Z" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            Cari Instansi Darurat Terdekat
                        </button>
                    </div>
                </div>
            </div>

            <!-- Google Maps Container -->
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
        
        // Variabel global untuk Google Maps
        let map;
        let markers = [];
        let infoWindows = [];
        let instansiMarkers = []; // Marker untuk instansi darurat
        const defaultCenter = { lat: -6.2088, lng: 106.8456 }; // Jakarta, Indonesia
        
        // CATATAN OPTIMASI PLACES API:
        // ============================================
        // 1. Autocomplete DIHAPUS - Autocomplete dipanggil setiap kali user mengetik,
        //    yang bisa menghabiskan ratusan credit per hari. DIHAPUS untuk menghemat.
        // 
        // 2. TOMBOL MANUAL - Places API hanya dipanggil saat admin menekan tombol
        //    "Cari Instansi Darurat Terdekat". Ini mengurangi request dari ratusan/jam
        //    menjadi hanya beberapa kali per hari.
        // 
        // 3. CACHE DATABASE - Sebelum memanggil Places API, sistem cek dulu data di database.
        //    Jika sudah ada instansi dalam radius yang sama, gunakan data dari database.
        //    Ini bisa menghemat 80-90% credit Places API.
        // 
        // 4. BATASI QUERY - Hanya cari: hospital, police, fire_station
        //    Radius maksimal: 5000 meter
        //    Ini mengurangi jumlah hasil dan credit yang digunakan.
        // 
        // RISIKO BIAYA PLACES API:
        // - Autocomplete: $2.83 per 1000 requests
        // - Nearby Search: $32 per 1000 requests
        // - Tanpa optimasi ini, aplikasi bisa menghabiskan ratusan dollar per bulan
        // - Dengan optimasi ini, biaya bisa turun menjadi < $10 per bulan

        // Inisialisasi Google Maps
        function initMap() {
            // Buat peta
            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultCenter,
                zoom: 11,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            // Tampilkan semua marker
            displayMarkers(reportsData);

            // Event listener untuk filter
            document.getElementById('filterStatus').addEventListener('change', filterMarkers);
            document.getElementById('filterCategory').addEventListener('change', filterMarkers);
            
            // Event listener untuk tombol "Cari Instansi Darurat Terdekat"
            const findInstansiBtn = document.getElementById('findInstansiBtn');
            if (findInstansiBtn) {
                findInstansiBtn.addEventListener('click', function() {
                    findNearbyInstansi();
                });
            }
        }
        
        // Fungsi untuk mencari instansi darurat terdekat
        // CATATAN: Fungsi ini memicu request ke Places API
        // Request hanya dilakukan saat admin menekan tombol manual
        function findNearbyInstansi() {
            const btn = document.getElementById('findInstansiBtn');
            const center = map.getCenter();
            
            if (!center) {
                alert('Peta belum siap. Silakan tunggu sebentar.');
                return;
            }
            
            // Disable tombol saat loading
            btn.disabled = true;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span>Mencari instansi...</span>';
            
            // Ambil koordinat center peta
            const lat = center.lat();
            const lng = center.lng();
            
            // Buat FormData untuk POST request
            const formData = new FormData();
            formData.append('latitude', lat);
            formData.append('longitude', lng);
            formData.append('jenis_instansi', 'all'); // Cari semua jenis: hospital, police, fire_station
            
            // Request ke endpoint PHP
            fetch('api_find_instansi.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                
                if (data.success) {
                    // Tampilkan marker instansi di peta
                    displayInstansiMarkers(data.data, data.from_cache);
                    
                    // Tampilkan notifikasi
                    const message = data.from_cache 
                        ? `Ditemukan ${data.count} instansi dari cache database (tidak menggunakan credit Places API)`
                        : `Ditemukan ${data.count} instansi dari Google Places API. ${data.saved_to_cache} instansi disimpan ke cache.`;
                    
                    alert(message);
                } else {
                    alert('Error: ' + (data.message || 'Tidak dapat mencari instansi'));
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mencari instansi. Silakan coba lagi.');
            });
        }
        
        // Fungsi untuk menampilkan marker instansi di peta
        function displayInstansiMarkers(instansiData, fromCache) {
            // Hapus marker instansi lama
            clearInstansiMarkers();
            
            if (!instansiData || instansiData.length === 0) {
                return;
            }
            
            // Icon berbeda untuk setiap jenis instansi
            const iconMap = {
                'hospital': {
                    path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                    scale: 6,
                    fillColor: '#dc3545', // Merah untuk rumah sakit
                    fillOpacity: 0.8,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                },
                'police': {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    fillColor: '#007bff', // Biru untuk polisi
                    fillOpacity: 0.8,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                },
                'fire_station': {
                    path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                    scale: 6,
                    fillColor: '#ff6b00', // Orange untuk pemadam kebakaran
                    fillOpacity: 0.8,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                }
            };
            
            instansiData.forEach(function(instansi) {
                const lat = parseFloat(instansi.latitude || instansi.lat);
                const lng = parseFloat(instansi.longitude || instansi.lng);
                
                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }
                
                // Tentukan jenis instansi dari places_type atau types
                let jenis = 'hospital'; // default
                if (instansi.places_type) {
                    jenis = instansi.places_type;
                } else if (instansi.types && Array.isArray(instansi.types)) {
                    if (instansi.types.includes('police')) {
                        jenis = 'police';
                    } else if (instansi.types.includes('fire_station')) {
                        jenis = 'fire_station';
                    }
                }
                
                const position = { lat: lat, lng: lng };
                const icon = iconMap[jenis] || iconMap['hospital'];
                
                // Buat marker
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    icon: icon,
                    title: instansi.nama || instansi.name || 'Instansi Darurat',
                    animation: google.maps.Animation.DROP
                });
                
                // Buat info window
                const infoWindowContent = `
                    <div class="info-window-content">
                        <div class="info-window-title">${escapeHtml(instansi.nama || instansi.name || 'Instansi Darurat')}</div>
                        <div class="info-window-detail">
                            <strong>Jenis:</strong> ${escapeHtml(jenis === 'hospital' ? 'Rumah Sakit' : jenis === 'police' ? 'Kantor Polisi' : 'Pemadam Kebakaran')}
                        </div>
                        <div class="info-window-detail">
                            <strong>Alamat:</strong> ${escapeHtml(instansi.alamat || instansi.alamat_lengkap || instansi.formatted_address || 'Tidak diketahui')}
                        </div>
                        ${instansi.rating ? `<div class="info-window-detail"><strong>Rating:</strong> ${instansi.rating}/5</div>` : ''}
                        ${instansi.distance_meters ? `<div class="info-window-detail"><strong>Jarak:</strong> ${Math.round(instansi.distance_meters)} meter</div>` : ''}
                        ${fromCache ? '<div class="info-window-time" style="color: #28a745; margin-top: 8px;"><small>✓ Data dari cache database</small></div>' : ''}
                    </div>
                `;
                
                const infoWindow = new google.maps.InfoWindow({
                    content: infoWindowContent
                });
                
                // Event listener untuk klik marker
                marker.addListener('click', function() {
                    // Tutup semua info window
                    infoWindows.forEach(function(iw) {
                        iw.close();
                    });
                    instansiMarkers.forEach(function(m) {
                        if (m.infoWindow) {
                            m.infoWindow.close();
                        }
                    });
                    
                    // Buka info window untuk marker ini
                    infoWindow.open(map, marker);
                });
                
                marker.infoWindow = infoWindow;
                instansiMarkers.push(marker);
            });
        }
        
        // Fungsi untuk menghapus semua marker instansi
        function clearInstansiMarkers() {
            instansiMarkers.forEach(function(marker) {
                marker.setMap(null);
            });
            instansiMarkers = [];
        }

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

        // Fungsi untuk mendapatkan icon marker
        function getMarkerIcon(status, urgent) {
            const color = getMarkerColor(status);
            const iconSize = urgent ? 40 : 32;
            
            return {
                path: google.maps.SymbolPath.CIRCLE,
                scale: iconSize / 2,
                fillColor: color,
                fillOpacity: 0.8,
                strokeColor: '#ffffff',
                strokeWeight: 2
            };
        }

        // Fungsi untuk menampilkan marker
        function displayMarkers(reports) {
            // Hapus marker lama
            clearMarkers();

            if (reports.length === 0) {
                return;
            }

            // Buat bounds untuk fit semua marker
            const bounds = new google.maps.LatLngBounds();

            reports.forEach(function(report) {
                const lat = parseFloat(report.latitude);
                const lng = parseFloat(report.longitude);

                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }

                const position = { lat: lat, lng: lng };

                // Buat marker
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    icon: getMarkerIcon(report.status, report.urgent == 1),
                    title: report.title,
                    animation: google.maps.Animation.DROP
                });

                // Buat info window
                const infoWindowContent = createInfoWindowContent(report);
                const infoWindow = new google.maps.InfoWindow({
                    content: infoWindowContent
                });

                // Event listener untuk klik marker
                marker.addListener('click', function() {
                    // Tutup semua info window
                    infoWindows.forEach(function(iw) {
                        iw.close();
                    });
                    
                    // Buka info window untuk marker ini
                    infoWindow.open(map, marker);
                });

                markers.push(marker);
                infoWindows.push(infoWindow);
                bounds.extend(position);
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
                <div class="info-window-content">
                    <div class="info-window-title">${escapeHtml(report.title)}</div>
                    <div class="info-window-detail">
                        <strong>Jenis Laporan:</strong> ${categoryText[report.category] || report.category}
                    </div>
                    <div class="info-window-detail">
                        <strong>Status:</strong> ${statusText[report.status] || report.status}
                    </div>
                    <div class="info-window-detail">
                        <strong>Pelapor:</strong> ${escapeHtml(report.user_fullname || report.username || 'Tidak diketahui')}
                    </div>
                    <div class="info-window-detail">
                        <strong>Lokasi:</strong> ${escapeHtml(report.location)}
                    </div>
                    <div class="info-window-detail" style="margin-top: 8px;">
                        ${escapeHtml(report.description.substring(0, 100))}${report.description.length > 100 ? '...' : ''}
                    </div>
                    <div class="info-window-time">
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
                marker.setMap(null);
            });
            markers = [];
            infoWindows = [];
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

