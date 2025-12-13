/**
 * PROJECT ONE - MAIN JAVASCRIPT
 * Frontend interactions and animations
 */

(function() {
    'use strict';

    // ===== MOBILE NAVIGATION TOGGLE =====
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }

    // ===== SMOOTH SCROLL FOR ANCHOR LINKS =====
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
                
                // Close mobile menu if open
                if (navMenu) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                }
            }
        });
    });

    // ===== SCROLL ANIMATIONS =====
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

    // Observe elements for fade-in animation
    document.addEventListener('DOMContentLoaded', function() {
        const elementsToAnimate = document.querySelectorAll(
            '.feature-card, .about-text, .about-visual, .contact-card, .cta-card'
        );
        
        elementsToAnimate.forEach(el => {
            el.classList.add('fade-in');
            observer.observe(el);
        });
    });

    // ===== NAVBAR SCROLL EFFECT =====
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

    // ===== PARALLAX EFFECT FOR HERO =====
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const heroBackground = document.querySelector('.hero-background');
        
        if (heroBackground && scrolled < window.innerHeight) {
            heroBackground.style.transform = `translateY(${scrolled * 0.5}px)`;
            heroBackground.style.opacity = 1 - (scrolled / window.innerHeight) * 0.3;
        }
    });

    // ===== BUTTON HOVER EFFECTS =====
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });

    // ===== CARD HOVER ENHANCEMENT =====
    document.querySelectorAll('.feature-card, .contact-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });

    // ===== FORM VALIDATION (for future forms) =====
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

    // Export for future use
    window.ProjectOne = {
        validateForm: validateForm
    };

    // ===== CONSOLE MESSAGE =====
    console.log('%cProject One', 'color: #0A2540; font-size: 24px; font-weight: bold;');
    console.log('%cSolusi Cepat untuk Situasi Darurat', 'color: #E63946; font-size: 14px;');

})();

