// JavaScript principal pour EC01 - Landing Page SkillHub

// Gestion de la navigation fluide
document.addEventListener('DOMContentLoaded', function() {
    // Navigation fluide vers les sections
    const navLinks = document.querySelectorAll('nav a[href^="#"]');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                e.preventDefault();
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animation du bouton "Inscription Apprenant"
    const inscriptionButton = document.querySelector('#hero-section button');
    if (inscriptionButton) {
        inscriptionButton.addEventListener('click', function() {
            // Redirection vers la page d'inscription
            window.location.href = '../EC02/dashboard/inscription.html';
        });
    }

    // Animation des cartes de valeurs
    const valeurArticles = document.querySelectorAll('#nos-valeurs article');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    valeurArticles.forEach(article => {
        article.style.opacity = '0';
        article.style.transform = 'translateY(20px)';
        article.style.transition = 'opacity 0.5s, transform 0.5s';
        observer.observe(article);
    });
});

console.log('Landing Page SkillHub - EC01 chargée avec succès!');
