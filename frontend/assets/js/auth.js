/**
 * PROJECT ONE - AUTHENTICATION JAVASCRIPT
 * Form interactions and Google Sign-In
 */

(function() {
    'use strict';

    // ===== PASSWORD TOGGLE =====
    // For login page
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('active');
        });
    }

    // For register page - password field
    const togglePassword = document.getElementById('togglePassword');
    const registerPassword = document.getElementById('password');
    
    if (togglePassword && registerPassword) {
        togglePassword.addEventListener('click', function() {
            const type = registerPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            registerPassword.setAttribute('type', type);
            
            const eyeIcon = this.querySelector('.eye-icon');
            const eyeOffIcon = this.querySelector('.eye-off-icon');
            
            if (type === 'text') {
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        });
    }

    // For register page - confirm password field
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            
            const eyeIcon = this.querySelector('.eye-icon');
            const eyeOffIcon = this.querySelector('.eye-off-icon');
            
            if (type === 'text') {
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        });
    }

    // ===== FORM VALIDATION =====
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            // Basic client-side validation
            if (!username || !password) {
                e.preventDefault();
                showError('Username/email dan password harus diisi');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showError('Password minimal 6 karakter');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Memproses...</span>';
            }
        });
    }

    // ===== GOOGLE SIGN-IN =====
    const googleSignInBtn = document.getElementById('googleSignIn');
    
    if (googleSignInBtn) {
        googleSignInBtn.addEventListener('click', function() {
            // Redirect to Google OAuth
            window.location.href = '../../backend/auth/google_login.php';
        });
    }

    // ===== INPUT FOCUS EFFECTS =====
    const inputs = document.querySelectorAll('.form-input');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    // ===== AUTO-HIDE ALERTS =====
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        if (alert.classList.contains('alert-success')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        }
    });

    // ===== HELPER FUNCTIONS =====
    function showError(message) {
        // Remove existing error alerts
        const existingAlerts = document.querySelectorAll('.alert-error');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new error alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.innerHTML = `
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>${message}</span>
        `;
        
        const form = document.getElementById('loginForm');
        if (form) {
            form.insertBefore(alert, form.firstChild);
            
            // Scroll to alert
            alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // ===== ENTER KEY SUBMIT =====
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('form-input')) {
            const form = document.getElementById('loginForm');
            if (form) {
                form.requestSubmit();
            }
        }
    });

    // ===== CONSOLE MESSAGE =====
    console.log('%cProject One - Authentication', 'color: #0A2540; font-size: 16px; font-weight: bold;');
    console.log('%cSecure Login System', 'color: #4DA3FF; font-size: 12px;');

})();

