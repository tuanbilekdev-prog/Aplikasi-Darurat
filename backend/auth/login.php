<?php
/**
 * PROJECT ONE - HALAMAN MASUK
 * Halaman masuk modern dengan dukungan Google OAuth
 */

session_start();

// Jika sudah masuk, arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? 'user';
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}

// Ambil pesan error jika ada
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Project One</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Google Sign-In API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <!-- Back Button -->
        <a href="../../frontend/index.php" class="back-button" aria-label="Kembali ke beranda">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Kembali</span>
        </a>

        <!-- Left Side: Branding -->
        <div class="auth-branding">
            <div class="branding-content">
                <a href="../../frontend/index.php" class="brand-logo">
                    <svg class="logo" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                        <path d="M60 10 L20 25 L20 55 C20 75 40 95 60 110 C80 95 100 75 100 55 L100 25 Z" 
                              fill="#FFFFFF" stroke="#4DA3FF" stroke-width="2"/>
                        <circle cx="60" cy="50" r="20" fill="#E63946" opacity="0.9"/>
                        <circle cx="60" cy="50" r="12" fill="#FFFFFF"/>
                        <circle cx="60" cy="50" r="30" fill="none" stroke="#4DA3FF" stroke-width="1.5" opacity="0.4"/>
                        <circle cx="60" cy="50" r="38" fill="none" stroke="#4DA3FF" stroke-width="1" opacity="0.2"/>
                    </svg>
                    <span class="logo-text">Project One</span>
                </a>
                <h1 class="branding-title">Solusi Cepat untuk Situasi Darurat</h1>
                <p class="branding-description">
                    Masuk ke akun Anda untuk melaporkan kejadian darurat dengan cepat, 
                    aman, dan real-time. Sistem terpercaya untuk perlindungan Anda.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="auth-form-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <h2 class="auth-title">Masuk ke Project One</h2>
                    <p class="auth-subtitle">Laporkan kejadian darurat dengan cepat dan aman</p>
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

                <form action="process_login.php" method="POST" class="login-form" id="loginForm">
                    <div class="form-group">
                        <label for="username" class="form-label">Username atau Email</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                placeholder="Masukkan username atau email"
                                required
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Ingat saya</span>
                        </label>
                        <a href="forgot_password.php" class="forgot-link">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span>Masuk</span>
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>

                <div class="auth-divider">
                    <span>atau</span>
                </div>

                <!-- Google Sign-In Button -->
                <div class="google-login-wrapper">
                    <button type="button" class="btn btn-google" id="googleSignIn">
                        <svg class="google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Masuk dengan Google</span>
                    </button>
                </div>

                <div class="auth-footer">
                    <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/auth.js"></script>
    
    <script>
        // Penanganan tombol Google Sign-In
        document.getElementById('googleSignIn').addEventListener('click', function() {
            window.location.href = 'google_login.php';
        });
    </script>
</body>
</html>

