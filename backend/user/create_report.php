<?php
/**
 * PROJECT ONE - HALAMAN BUAT LAPORAN
 * Halaman untuk membuat laporan darurat baru
 */

session_start();
// Load Docker config jika di Docker environment, jika tidak load config.php biasa
if (file_exists(__DIR__ . '/../config.docker.php') && getenv('DB_HOST') === 'db') {
    require_once __DIR__ . '/../config.docker.php';
} else {
    require_once __DIR__ . '/../config.php';
}
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
    
    <!-- Leaflet.js - OpenStreetMap (GRATIS, tidak perlu API key) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                <form action="process_report.php" method="POST" enctype="multipart/form-data" class="report-form" id="reportForm">
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

                    <!-- OpenStreetMap Container (Leaflet.js) -->
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

                    <div class="form-group">
                        <label for="photos" class="form-label">Foto Kejadian (Opsional)</label>
                        <input 
                            type="file" 
                            id="photos" 
                            name="photos[]" 
                            class="form-input" 
                            accept="image/jpeg,image/png,image/jpg,image/gif"
                            multiple
                        >
                        <small style="color: var(--text-light); font-size: 0.875rem; display: block; margin-top: 8px;">
                            Anda dapat mengupload beberapa foto (maksimal 5 foto, ukuran maksimal 5MB per foto). Format: JPG, PNG, GIF
                        </small>
                        <div id="photoPreview" style="margin-top: 16px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;"></div>
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
        // Preview foto sebelum upload
        const photoInput = document.getElementById('photos');
        const photoPreview = document.getElementById('photoPreview');
        
        if (photoInput && photoPreview) {
            photoInput.addEventListener('change', function(e) {
                photoPreview.innerHTML = ''; // Clear preview sebelumnya
                
                const files = Array.from(e.target.files);
                
                // Validasi jumlah file (maksimal 5)
                if (files.length > 5) {
                    alert('Maksimal 5 foto yang dapat diupload');
                    this.value = '';
                    photoPreview.innerHTML = '';
                    return;
                }
                
                // Validasi ukuran dan tampilkan preview
                files.forEach((file, index) => {
                    // Validasi ukuran (5MB = 5 * 1024 * 1024 bytes)
                    if (file.size > 5 * 1024 * 1024) {
                        alert(`File "${file.name}" terlalu besar. Maksimal 5MB per foto.`);
                        return;
                    }
                    
                    // Validasi tipe file
                    if (!file.type.match('image.*')) {
                        alert(`File "${file.name}" bukan gambar.`);
                        return;
                    }
                    
                    // Tampilkan preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.style.position = 'relative';
                        div.style.border = '2px solid var(--border)';
                        div.style.borderRadius = '8px';
                        div.style.overflow = 'hidden';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100%';
                        img.style.height = '150px';
                        img.style.objectFit = 'cover';
                        img.style.display = 'block';
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.innerHTML = '×';
                        removeBtn.style.position = 'absolute';
                        removeBtn.style.top = '4px';
                        removeBtn.style.right = '4px';
                        removeBtn.style.background = 'rgba(220, 53, 69, 0.9)';
                        removeBtn.style.color = 'white';
                        removeBtn.style.border = 'none';
                        removeBtn.style.borderRadius = '50%';
                        removeBtn.style.width = '24px';
                        removeBtn.style.height = '24px';
                        removeBtn.style.cursor = 'pointer';
                        removeBtn.style.fontSize = '18px';
                        removeBtn.style.lineHeight = '1';
                        removeBtn.onclick = function() {
                            // Hapus file dari input
                            const dt = new DataTransfer();
                            Array.from(photoInput.files).forEach((f, i) => {
                                if (i !== index) {
                                    dt.items.add(f);
                                }
                            });
                            photoInput.files = dt.files;
                            div.remove();
                        };
                        
                        div.appendChild(img);
                        div.appendChild(removeBtn);
                        photoPreview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }
        
        // Penghitung karakter
        const description = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        if (description && charCount) {
            description.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
        }

        // Variabel global untuk Leaflet Map
        let map;
        let marker;
        const defaultCenter = [ -6.2088, 106.8456 ]; // Jakarta, Indonesia [lat, lng]

        // Inisialisasi Leaflet Map (OpenStreetMap - GRATIS, tidak perlu API key)
        document.addEventListener('DOMContentLoaded', function() {
            // Buat peta
            map = L.map('map').setView(defaultCenter, 13);

            // Tambahkan tile layer dari OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            const locationInput = document.getElementById('location');

            // Event listener untuk klik peta
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                // Set koordinat ke input hidden
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                // Hapus marker lama jika ada
                if (marker) {
                    map.removeLayer(marker);
                }

                // Buat marker baru (dapat di-drag)
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);

                // Update koordinat saat marker di-drag
                marker.on('dragend', function(event) {
                    const newLat = event.target.getLatLng().lat;
                    const newLng = event.target.getLatLng().lng;
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
                                map.setView([lat, lng], 15);
                                
                                // Hapus marker lama jika ada
                                if (marker) {
                                    map.removeLayer(marker);
                                }
                                
                                // Buat marker baru (dapat di-drag)
                                marker = L.marker([lat, lng], {
                                    draggable: true
                                }).addTo(map);
                                
                                // Update koordinat saat marker di-drag
                                marker.on('dragend', function(event) {
                                    const newLat = event.target.getLatLng().lat;
                                    const newLng = event.target.getLatLng().lng;
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
            // Menggunakan PHP proxy endpoint untuk menghindari CORS dan mengatur User-Agent dengan benar
            function reverseGeocode(lat, lng, retryCount = 0) {
                const maxRetries = 2;
                const url = `reverse_geocode.php?lat=${lat}&lng=${lng}`;
                
                // Set loading state
                if (locationInput) {
                    locationInput.placeholder = 'Mendapatkan alamat...';
                }
                
                // Fetch ke PHP proxy endpoint (menghindari CORS dan mengatur User-Agent dengan benar)
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.display_name) {
                            // Gunakan display_name jika ada
                            locationInput.value = data.display_name;
                            if (locationInput) {
                                locationInput.placeholder = 'Alamat atau lokasi kejadian';
                            }
                        } else if (data && data.address) {
                            // Jika display_name tidak ada, buat alamat dari komponen address
                            const address = data.address;
                            const addressParts = [];
                            
                            // Urutan komponen alamat (dari detail ke umum)
                            if (address.road || address.street) addressParts.push(address.road || address.street);
                            if (address.house_number) addressParts.push(address.house_number);
                            if (address.suburb || address.neighbourhood) addressParts.push(address.suburb || address.neighbourhood);
                            if (address.village || address.city_district) addressParts.push(address.village || address.city_district);
                            if (address.city || address.town) addressParts.push(address.city || address.town);
                            if (address.state_district) addressParts.push(address.state_district);
                            if (address.state) addressParts.push(address.state);
                            if (address.postcode) addressParts.push(address.postcode);
                            if (address.country) addressParts.push(address.country);
                            
                            if (addressParts.length > 0) {
                                locationInput.value = addressParts.join(', ');
                            } else {
                                // Fallback: gunakan nama tempat atau daerah terdekat
                                const fallbackName = address.amenity || address.leisure || address.tourism || 
                                                    address.place || address.city || address.country;
                                if (fallbackName) {
                                    locationInput.value = `Lokasi di ${fallbackName}`;
                                } else {
                                    // Jika benar-benar tidak ada data, biarkan kosong
                                    locationInput.value = '';
                                    if (locationInput) {
                                        locationInput.placeholder = 'Silakan isi alamat lokasi kejadian secara manual';
                                    }
                                }
                            }
                            if (locationInput) {
                                locationInput.placeholder = 'Alamat atau lokasi kejadian';
                            }
                        } else {
                            // Retry dengan zoom level yang lebih rendah jika belum max retries
                            if (retryCount < maxRetries) {
                                setTimeout(() => {
                                    reverseGeocode(lat, lng, retryCount + 1);
                                }, 800);
                                return;
                            }
                            
                            // Fallback terakhir: biarkan kosong dengan placeholder yang jelas
                            // User harus mengisi manual jika Nominatim gagal
                            locationInput.value = '';
                            if (locationInput) {
                                locationInput.placeholder = 'Silakan isi alamat lokasi kejadian secara manual';
                            }
                            console.warn('Reverse geocode: Tidak dapat mendapatkan alamat lengkap dari Nominatim setelah ' + (retryCount + 1) + ' percobaan');
                        }
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        console.error('Reverse geocode error:', error);
                        
                        // Retry jika belum max retries
                        if (retryCount < maxRetries) {
                            setTimeout(() => {
                                reverseGeocode(lat, lng, retryCount + 1);
                            }, 1000);
                            return;
                        }
                        
                        // Fallback terakhir: biarkan kosong dengan placeholder yang jelas
                        // User harus mengisi manual jika Nominatim gagal
                        if (locationInput) {
                            locationInput.value = '';
                            locationInput.placeholder = 'Silakan isi alamat lokasi kejadian secara manual';
                        }
                        console.warn('Reverse geocode: Gagal mendapatkan alamat dari Nominatim setelah retry');
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
        });
    </script>
</body>
</html>

