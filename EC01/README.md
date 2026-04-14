# EC01 - Landing Page SkillHub

## 📋 Description
Landing page publique conforme HTML5 sémantique, WCAG AA, responsive design (sans framework).

## ✅ Conformité EC01
- HTML5 sémantique (header, main, footer, section, article)
- Hiérarchie des titres : 1 h1, 4 h2, 6+ h3
- Accessibilité WCAG AA : skip link, ARIA landmarks, contrasts 4.5:1+
- Responsive mobile-first : Flexbox + CSS Grid, menu hamburger
- Vanilla CSS/JS : variables CSS, IntersectionObserver, pas de framework
- SEO : meta tags, images alt descriptives, loading lazy/eager
- Animations : IntersectionObserver au scroll

## 📂 Structure
```
EC01/
├── index.html          # Page principale
├── css/style.css      # Styles (variables, flexbox, grid)
├── js/main.js        # Interactivité (menu, animations)
├── inscription.html  # Inscription
└── connexion.html    # Connexion
```

## 🚀 Lancer le projet

### Pourquoi un serveur local ?
Ouvrir `index.html` directement peut bloquer certaines fonctionnalités (CORS, modules ES, fetch). Un serveur local simule un environnement réel.

### Options disponibles
```bash
# Option 1 : Python (recommandé, préinstallé sur macOS)
python3 -m http.server 8000
# http://localhost:8000

# Option 2 : Node.js (si installé)
npx serve
# http://localhost:3000

# Option 3 : PHP (si installé)
php -S localhost:8000
# http://localhost:8000

# Option 4 : VS Code (Live Server extension)
# Clic droit > Open with Live Server
```

## 📱 Sections
- Header (navigation + menu mobile)
- Hero (image + CTA)
- Nos Valeurs (3 cartes)
- Nos Formations (3 catégories)
- Inscription CTA
- Footer

## 📊 Validation
- Lighthouse : 95+ Performance, 100 A11y/SEO
- WAVE : 0 erreurs, contrastes WCAG AA
- W3C Validator : HTML/CSS valides

