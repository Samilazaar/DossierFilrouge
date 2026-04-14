// JavaScript principal pour EC01 - Landing Page SkillHub

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== MENU MOBILE =====
    const menuToggle = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            const isActive = navMenu.classList.toggle('active');
            const ariaExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !ariaExpanded);
            this.setAttribute('aria-label', isActive ? 'Fermer le menu' : 'Ouvrir le menu');
        });
    }
    
    // Fermer menu mobile au clic sur lien
    const navLinksMobile = document.querySelectorAll('.nav-menu a');
    navLinksMobile.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.setAttribute('aria-label', 'Ouvrir le menu');
            }
        });
    });
    
    // ===== NAVIGATION FLUIDE (SMOOTH SCROLL) =====
    const navLinks = document.querySelectorAll('nav a[href^="#"]');
    const headerHeight = document.querySelector('header')?.offsetHeight || 70;
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                e.preventDefault();
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ===== ANIMATION DES CARTES VALEURS =====
    const valeurArticles = document.querySelectorAll('.valeur-card');
    const valeursObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    valeurArticles.forEach((article, index) => {
        article.style.opacity = '0';
        article.style.transform = 'translateY(30px)';
        article.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        valeursObserver.observe(article);
    });
    
    // ===== ANIMATION DES CARTES FORMATIONS =====
    const formationArticles = document.querySelectorAll('.formation-card');
    const formationsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    formationArticles.forEach((article, index) => {
        article.style.opacity = '0';
        article.style.transform = 'translateY(30px)';
        article.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        formationsObserver.observe(article);
    });

    // ===== ANIMATION DES CARTES FORMATEURS =====
    const formateurArticles = document.querySelectorAll('.formateur-card');
    const formateursObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    formateurArticles.forEach((article, index) => {
        article.style.opacity = '0';
        article.style.transform = 'translateY(30px)';
        article.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        formateursObserver.observe(article);
    });

    // ===== REDIRECTION BOUTON HERO =====
    const heroButton = document.querySelector('.hero-button');
    if (heroButton) {
        heroButton.addEventListener('click', function() {
            const inscriptionSection = document.querySelector('#inscription');
            if (inscriptionSection) {
                const targetPosition = inscriptionSection.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    }
    
    console.log('🚀 Landing Page SkillHub - EC01 chargée avec succès!');
});
