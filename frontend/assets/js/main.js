/**
 * PROJECT ONE - JAVASCRIPT UTAMA
 * Interaksi frontend dan animasi
 */

(function() {
    'use strict';

    // ===== TOGGLE NAVIGASI MOBILE =====
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }

    // ===== SCROLL HALUS UNTUK TAUTAN ANCHOR =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
                
                // Tutup menu mobile jika terbuka
                if (navMenu) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                }
            }
        });
    });

    // ===== ANIMASI SCROLL =====
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Amati elemen untuk animasi fade-in
    document.addEventListener('DOMContentLoaded', function() {
        const elementsToAnimate = document.querySelectorAll(
            '.feature-card, .about-text, .about-visual, .contact-card, .cta-card'
        );
        
        elementsToAnimate.forEach(el => {
            el.classList.add('fade-in');
            observer.observe(el);
        });
    });

    // ===== EFEK SCROLL NAVBAR =====
    let lastScroll = 0;
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            navbar.style.boxShadow = '0 4px 16px rgba(10, 37, 64, 0.15)';
        } else {
            navbar.style.boxShadow = '0 2px 8px rgba(10, 37, 64, 0.08)';
        }
        
        lastScroll = currentScroll;
    });

    // ===== EFEK PARALLAX UNTUK HERO =====
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const heroBackground = document.querySelector('.hero-background');
        
        if (heroBackground && scrolled < window.innerHeight) {
            heroBackground.style.transform = `translateY(${scrolled * 0.5}px)`;
            heroBackground.style.opacity = 1 - (scrolled / window.innerHeight) * 0.3;
        }
    });

    // ===== EFEK HOVER TOMBOL =====
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });

    // ===== PENINGKATAN HOVER KARTU =====
    document.querySelectorAll('.feature-card, .contact-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });

    // ===== VALIDASI FORMULIR (untuk formulir di masa depan) =====
    const validateForm = function(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });
        
        return isValid;
    };

    // Ekspor untuk penggunaan di masa depan
    window.ProjectOne = {
        validateForm: validateForm
    };

    // ===== PESAN KONSOL =====
    console.log('%cProject One', 'color: #0A2540; font-size: 24px; font-weight: bold;');
    console.log('%cSolusi Cepat untuk Situasi Darurat', 'color: #E63946; font-size: 14px;');

})();

