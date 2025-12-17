<?php
// Ambil halaman saat ini untuk menetapkan tautan navigasi aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="user-navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="dashboard.php" class="logo-link">
                <svg class="logo" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                    <path d="M60 10 L20 25 L20 55 C20 75 40 95 60 110 C80 95 100 75 100 55 L100 25 Z" 
                          fill="#0A2540" stroke="#4DA3FF" stroke-width="2"/>
                    <circle cx="60" cy="50" r="20" fill="#E63946" opacity="0.9"/>
                    <circle cx="60" cy="50" r="12" fill="#FFFFFF"/>
                    <circle cx="60" cy="50" r="30" fill="none" stroke="#4DA3FF" stroke-width="1.5" opacity="0.4"/>
                    <circle cx="60" cy="50" r="38" fill="none" stroke="#4DA3FF" stroke-width="1" opacity="0.2"/>
                </svg>
                <span class="logo-text">Project One</span>
            </a>
        </div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="nav-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="history.php" class="nav-link <?php echo ($current_page === 'history.php') ? 'active' : ''; ?>">Riwayat</a></li>
            <li><a href="profile.php" class="nav-link <?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>">Profil</a></li>
        </ul>
        <div class="nav-actions">
            <a href="../auth/logout.php" class="btn-logout">Keluar</a>
        </div>
        <button class="nav-toggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>
