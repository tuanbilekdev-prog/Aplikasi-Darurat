<?php
/**
 * PROJECT ONE - HALAMAN BUAT LAPORAN
 * Halaman untuk membuat laporan darurat baru
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
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - Project One</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/user-dashboard.css">
    <link rel="stylesheet" href="../../frontend/assets/css/maps.css">
    
    <!-- Google Maps API -->
    <!-- CATATAN: libraries=places DIHAPUS untuk menghemat credit Places API -->
    <!-- User form hanya menggunakan Geocoding API (gratis) untuk reverse geocode, bukan Places API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : 'YOUR_GOOGLE_MAPS_API_KEY'; ?>&callback=initMap" async defer></script>
</head>
<body>
    <?php include '../partials/user_navbar.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Buat Laporan Darurat</h1>
                <p class="page-subtitle">Isi form di bawah ini untuk melaporkan kejadian darurat</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form action="process_report.php" method="POST" class="report-form" id="reportForm">
                    <div class="form-group">
                        <label for="title" class="form-label">Judul Laporan <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input" 
                            placeholder="Contoh: Kecelakaan di Jalan Raya"
                            required
                            maxlength="100"
                        >
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Kategori <span class="required">*</span></label>
                        <select id="category" name="category" class="form-input" required>
                            <option value="">Pilih kategori</option>
                            <option value="kecelakaan">Kecelakaan</option>
                            <option value="kebakaran">Kebakaran</option>
                            <option value="medis">Darurat Medis</option>
                            <option value="kejahatan">Kejahatan</option>
                            <option value="bencana">Bencana Alam</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Deskripsi <span class="required">*</span></label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-input form-textarea" 
                            rows="6"
                            placeholder="Jelaskan kejadian darurat secara detail..."
                            required
                            maxlength="1000"
                        ></textarea>
                        <div class="char-count"><span id="charCount">0</span>/1000 karakter</div>
                    </div>

                    <div class="form-group">
                        <label for="location" class="form-label">Lokasi <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            class="form-input" 
                            placeholder="Alamat atau lokasi kejadian"
                            required
                        >
                        <div class="map-controls">
                            <button type="button" class="btn-location" id="getLocationBtn">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 5.02944 7.02944 1 12 1C16.9706 1 21 5.02944 21 10Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Gunakan Lokasi Saya
                            </button>
                        </div>
                    </div>

                    <!-- Input Hidden untuk Koordinat -->
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                    <!-- Google Maps Container -->
                    <div class="form-group">
                        <label class="form-label">Pilih Lokasi di Peta <span class="required">*</span></label>
                        <div id="map" class="map-container"></div>
                        <p class="map-hint">Klik pada peta untuk memilih lokasi kejadian, atau gunakan tombol "Gunakan Lokasi Saya" di atas</p>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="urgent" id="urgent" value="1">
                            <span>Laporan Darurat (Prioritas Tinggi)</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <span>Kirim Laporan</span>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include '../partials/user_footer.php'; ?>

    <script src="../../frontend/assets/js/user-dashboard.js"></script>
    <script>
        // Penghitung karakter
        const description = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        if (description && charCount) {
            description.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
        }

        // Variabel global untuk Google Maps
        let map;
        let marker;
        let geocoder;
        const defaultCenter = { lat: -6.2088, lng: 106.8456 }; // Jakarta, Indonesia

        // Inisialisasi Google Maps
        function initMap() {
            // Buat peta
            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultCenter,
                zoom: 13,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            geocoder = new google.maps.Geocoder();

            // Event listener untuk klik peta
            map.addListener('click', function(event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                
                // Set koordinat ke input hidden
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                // Hapus marker lama jika ada
                if (marker) {
                    marker.setMap(null);
                }

                // Buat marker baru
                marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                    draggable: true,
                    animation: google.maps.Animation.DROP
                });

                // Update koordinat saat marker di-drag
                marker.addListener('dragend', function(event) {
                    const newLat = event.latLng.lat();
                    const newLng = event.latLng.lng();
                    document.getElementById('latitude').value = newLat;
                    document.getElementById('longitude').value = newLng;
                    
                    // Reverse geocode untuk mendapatkan alamat
                    reverseGeocode(newLat, newLng);
                });

                // Reverse geocode untuk mendapatkan alamat
                reverseGeocode(lat, lng);
            });

            // Tombol "Gunakan Lokasi Saya"
            const getLocationBtn = document.getElementById('getLocationBtn');
            const locationInput = document.getElementById('location');
            
            if (getLocationBtn) {
                getLocationBtn.addEventListener('click', function() {
                    if (navigator.geolocation) {
                        this.disabled = true;
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<span>Mendapatkan lokasi...</span>';
                        
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;
                                
                                // Set koordinat ke input hidden
                                document.getElementById('latitude').value = lat;
                                document.getElementById('longitude').value = lng;
                                
                                // Pindahkan peta ke lokasi user
                                const userLocation = { lat: lat, lng: lng };
                                map.setCenter(userLocation);
                                map.setZoom(15);
                                
                                // Hapus marker lama jika ada
                                if (marker) {
                                    marker.setMap(null);
                                }
                                
                                // Buat marker baru
                                marker = new google.maps.Marker({
                                    position: userLocation,
                                    map: map,
                                    draggable: true,
                                    animation: google.maps.Animation.DROP
                                });
                                
                                // Update koordinat saat marker di-drag
                                marker.addListener('dragend', function(event) {
                                    const newLat = event.latLng.lat();
                                    const newLng = event.latLng.lng();
                                    document.getElementById('latitude').value = newLat;
                                    document.getElementById('longitude').value = newLng;
                                    reverseGeocode(newLat, newLng);
                                });
                                
                                // Reverse geocode untuk mendapatkan alamat
                                reverseGeocode(lat, lng);
                                
                                getLocationBtn.disabled = false;
                                getLocationBtn.innerHTML = originalHTML;
                            },
                            function(error) {
                                alert('Tidak dapat mendapatkan lokasi. Silakan pilih lokasi di peta atau isi manual.');
                                getLocationBtn.disabled = false;
                                getLocationBtn.innerHTML = originalHTML;
                            }
                        );
                    } else {
                        alert('Browser tidak mendukung geolocation');
                    }
                });
            }

            // Fungsi reverse geocode (koordinat ke alamat)
            // CATATAN: Fungsi ini menggunakan Geocoding API (GRATIS), bukan Places API
            // Geocoding API tidak dikenakan biaya untuk penggunaan normal
            // Ini berbeda dengan Places API yang dikenakan biaya per request
            function reverseGeocode(lat, lng) {
                geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        locationInput.value = results[0].formatted_address;
                    } else {
                        locationInput.value = `${lat}, ${lng}`;
                    }
                });
            }

            // Validasi form sebelum submit
            const reportForm = document.getElementById('reportForm');
            if (reportForm) {
                reportForm.addEventListener('submit', function(e) {
                    const lat = document.getElementById('latitude').value;
                    const lng = document.getElementById('longitude').value;
                    
                    if (!lat || !lng) {
                        e.preventDefault();
                        alert('Silakan pilih lokasi di peta atau gunakan tombol "Gunakan Lokasi Saya"');
                        return false;
                    }
                });
            }
        }
    </script>
</body>
</html>

