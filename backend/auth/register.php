<?php
/**
 * PROJECT ONE - REGISTRATION PAGE
 * Halaman registrasi untuk user (masyarakat)
 */

session_start();
require_once __DIR__ . '/../config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Project One</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../frontend/assets/css/auth.css">
</head>
<body>
    <!-- Back Button -->
    <a href="../../frontend/index.php" class="back-button">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>Kembali</span>
    </a>

    <div class="auth-container">
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
                </a>
                <h1 class="brand-title">Project One</h1>
                <p class="brand-subtitle">Sistem Pelaporan Darurat Terpercaya</p>
                <div class="brand-features">
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span>Laporan Cepat</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span>Aman & Terverifikasi</span>
                    </div>
                    <div class="feature-item">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span>Real-Time Monitoring</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Registration Form -->
        <div class="auth-form-wrapper">
            <div class="auth-form-card">
                <div class="form-header">
                    <h2 class="form-title">Daftar Akun Project One</h2>
                    <p class="form-subtitle">Buat akun untuk melaporkan kejadian darurat dengan aman</p>
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

                <form action="process_register.php" method="POST" class="auth-form" id="registerForm">
                    <div class="form-group">
                        <label for="fullname" class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname" 
                            class="form-input" 
                            placeholder="Masukkan nama lengkap Anda"
                            required
                            autocomplete="name"
                            value="<?php echo isset($_GET['fullname']) ? htmlspecialchars($_GET['fullname']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Pilih username unik"
                            required
                            autocomplete="username"
                            pattern="[a-zA-Z0-9_]{3,20}"
                            title="Username harus 3-20 karakter, hanya huruf, angka, dan underscore"
                            value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>"
                        >
                        <small class="form-hint">3-20 karakter, huruf, angka, dan underscore</small>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="nama@email.com"
                            required
                            autocomplete="email"
                            value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password <span class="required">*</span></label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Minimal 8 karakter"
                                required
                                autocomplete="new-password"
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                    <path d="M17.94 17.94C16.2306 19.243 14.1491 19.9649 12 20C5 20 1 12 1 12C2.24389 9.68192 3.96914 7.65663 6.06 6.06M14.12 14.12C13.5364 14.6833 12.8069 15.0811 12 15.2909M14.12 14.12L9.88 9.88M9.88 9.88C10.4636 9.31675 11.1931 8.91889 12 8.7091M9.88 9.88L6.06 6.06M6.06 6.06L2.5 2.5M17.94 17.94L21.5 21.5M14.12 14.12L17.94 17.94M9.88 9.88L6.06 6.06" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        <small class="form-hint">Minimal 8 karakter</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Konfirmasi Password <span class="required">*</span></label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="Ulangi password"
                                required
                                autocomplete="new-password"
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle password visibility">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                    <path d="M17.94 17.94C16.2306 19.243 14.1491 19.9649 12 20C5 20 1 12 1 12C2.24389 9.68192 3.96914 7.65663 6.06 6.06M14.12 14.12C13.5364 14.6833 12.8069 15.0811 12 15.2909M14.12 14.12L9.88 9.88M9.88 9.88C10.4636 9.31675 11.1931 8.91889 12 8.7091M9.88 9.88L6.06 6.06M6.06 6.06L2.5 2.5M17.94 17.94L21.5 21.5M14.12 14.12L17.94 17.94M9.88 9.88L6.06 6.06" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span>Daftar Akun</span>
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>

                <div class="form-footer">
                    <p>Sudah punya akun? <a href="login.php" class="link-primary">Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../../frontend/assets/js/auth.js"></script>
    <script>
        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const registerForm = document.getElementById('registerForm');

        function validatePasswordMatch() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        password.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);

        // Form submission validation
        registerForm.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok');
                confirmPassword.focus();
                return false;
            }
        });
    </script>
</body>
</html>

