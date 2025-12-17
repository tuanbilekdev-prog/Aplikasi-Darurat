<?php
/**
 * PROJECT ONE - HALAMAN UTAMA
 * Frontend: Halaman utama landing page
 */

// Backend: Muat konfigurasi
require_once __DIR__ . '/../backend/config.php';

// Periksa apakah pengguna sudah masuk (untuk penggunaan di masa depan)
$is_logged_in = isLoggedIn();
$user_role = getUserRole();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Project One - Solusi cepat untuk pelaporan darurat. Laporan darurat dengan cepat, aman, dan real-time.">
    <title><?php echo APP_NAME; ?> - Solusi Cepat untuk Situasi Darurat</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <!-- Hero Section -->
    <section id="beranda" class="hero">
        <div class="hero-background"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Solusi Cepat untuk<br>
                    <span class="highlight">Situasi Darurat</span>
                </h1>
                <p class="hero-subtitle">
                    Project One membantu Anda melaporkan kejadian darurat<br>
                    dengan cepat, aman, dan real-time.
                </p>
                <div class="hero-buttons">
                    <a href="../backend/auth/login.php" class="btn btn-primary">Laporkan Darurat</a>
                    <a href="#fitur" class="btn btn-secondary">Pelajari Lebih Lanjut</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Fitur Utama</h2>
                <p class="section-subtitle">Kemudahan dan kecepatan dalam genggaman Anda</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="32" cy="32" r="28" stroke="#4DA3FF" stroke-width="2" fill="none"/>
                            <path d="M32 12 L32 32 L44 44" stroke="#E63946" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Laporan Darurat Cepat</h3>
                    <p>Laporkan kejadian darurat dalam hitungan detik dengan antarmuka yang intuitif dan mudah digunakan.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="32" cy="32" r="20" stroke="#4DA3FF" stroke-width="2" fill="none"/>
                            <circle cx="32" cy="32" r="4" fill="#E63946"/>
                            <path d="M32 12 L32 20 M32 44 L32 52 M12 32 L20 32 M44 32 L52 32" stroke="#4DA3FF" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Lokasi Otomatis</h3>
                    <p>Sistem secara otomatis mendeteksi lokasi Anda untuk mempercepat proses penanganan darurat.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="8" y="16" width="48" height="36" rx="4" stroke="#4DA3FF" stroke-width="2" fill="none"/>
                            <circle cx="20" cy="28" r="2" fill="#E63946"/>
                            <circle cx="32" cy="28" r="2" fill="#E63946"/>
                            <circle cx="44" cy="28" r="2" fill="#E63946"/>
                            <path d="M20 40 L32 40 L44 40" stroke="#4DA3FF" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Monitoring Real-Time</h3>
                    <p>Pantau status laporan Anda secara real-time dan dapatkan notifikasi langsung saat ada update.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32 8 L40 24 L56 26 L44 38 L48 54 L32 46 L16 54 L20 38 L8 26 L24 24 Z" 
                                  stroke="#4DA3FF" stroke-width="2" fill="none"/>
                            <circle cx="32" cy="32" r="8" fill="#E63946" opacity="0.3"/>
                        </svg>
                    </div>
                    <h3>Aman & Terverifikasi</h3>
                    <p>Data Anda terlindungi dengan enkripsi tingkat tinggi dan sistem verifikasi yang ketat.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="section-title">Tentang Project One</h2>
                    <p class="about-description">
                        Project One adalah platform pelaporan darurat yang dirancang untuk memberikan 
                        solusi cepat dan terpercaya dalam menghadapi situasi darurat. Kami memahami 
                        bahwa setiap detik sangat berharga dalam keadaan darurat.
                    </p>
                    <p class="about-description">
                        Dengan teknologi terkini dan antarmuka yang user-friendly, Project One 
                        memungkinkan masyarakat untuk melaporkan kejadian darurat dengan mudah, 
                        sementara tim admin dapat merespons dan mengelola laporan dengan efisien.
                    </p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Layanan</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">100%</div>
                            <div class="stat-label">Terpercaya</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">Real-Time</div>
                            <div class="stat-label">Monitoring</div>
                        </div>
                    </div>
                </div>
                <div class="about-visual">
                    <div class="visual-card">
                        <div class="visual-shape shape-1"></div>
                        <div class="visual-shape shape-2"></div>
                        <div class="visual-shape shape-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-card">
                <h2 class="cta-title">Siap menghadapi keadaan darurat dengan lebih tenang?</h2>
                <p class="cta-subtitle">Bergabunglah dengan Project One dan dapatkan perlindungan terbaik untuk Anda dan keluarga</p>
                <a href="../backend/auth/login.php" class="btn btn-primary btn-large">Gunakan Project One Sekarang</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="kontak" class="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Hubungi Kami</h2>
                <p class="section-subtitle">Kami siap membantu Anda kapan saja</p>
            </div>
            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="8" y="12" width="48" height="40" rx="4" stroke="#4DA3FF" stroke-width="2" fill="none"/>
                            <path d="M8 20 L32 36 L56 20" stroke="#E63946" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Email</h3>
                    <p>info@projectone.id</p>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32 12 C24 12 18 18 18 26 C18 38 32 52 32 52 C32 52 46 38 46 26 C46 18 40 12 32 12 Z" 
                                  stroke="#4DA3FF" stroke-width="2" fill="none"/>
                            <circle cx="32" cy="26" r="6" fill="#E63946"/>
                        </svg>
                    </div>
                    <h3>Lokasi</h3>
                    <p>Indonesia</p>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">
                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 16 L28 20 L40 8 L44 16 L56 20 L52 32 L60 44 L48 48 L40 56 L28 52 L16 44 L20 32 Z" 
                                  stroke="#4DA3FF" stroke-width="2" fill="none"/>
                            <circle cx="32" cy="32" r="4" fill="#E63946"/>
                        </svg>
                    </div>
                    <h3>Telepon</h3>
                    <p>119</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'partials/footer.php'; ?>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>

