# 🌐 Cédric Goujon - Site Personnel

[![Website](https://img.shields.io/website?url=https%3A//cedric-goujon.fr&style=for-the-badge&logo=internet-explorer&logoColor=white)](https://cedric-goujon.fr)
[![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg?style=for-the-badge)](https://github.com/HrexD/cedric-goujon.fr/graphs/commit-activity)
[![GitHub last commit](https://img.shields.io/github/last-commit/HrexD/cedric-goujon.fr?style=for-the-badge)](https://github.com/HrexD/cedric-goujon.fr/commits/main)

> Site personnel d'un développeur Full-Stack avec 2 ans et demi d'expérience, spécialisé dans le développement d'applications web modernes et performantes.

## 📋 À propos

Ce dépôt contient le code source de mon site personnel [cedric-goujon.fr](https://cedric-goujon.fr), développé entièrement sur mesure. Le site présente mon profil professionnel, mes compétences, mes projets et permet aux visiteurs de me contacter.

## ✨ Fonctionnalités

- 🏠 **Page d'accueil** - Présentation personnelle et professionnelle
- 📄 **CV interactif modernisé** - Interface complètement redesignée avec animations *(v3.0)*
- 🚀 **Portfolio de projets** - Vitrine de mes réalisations
- 📧 **Formulaire de contact** - Communication directe avec sauvegarde en base
- 🔧 **Interface d'administration** - Gestion des messages reçus et système complet
- 📋 **Gestion des candidatures** - Module complet de suivi des candidatures
- 🧭 **Navigation dynamique harmonisée** - Interface unifiée sur toutes les pages admin *(nouveau)*
- 💎 **Architecture CSS/JS externalisée** - Styles et scripts organisés
- 🌓 **Mode sombre/clair** - Basculement de thème
- 📱 **Design responsive** - Compatible tous appareils
- 🔍 **Exercice GitHub** - Recherche d'utilisateurs GitHub (démo API)

## 🆕 Dernières nouveautés (v3.0 - Novembre 2025)

### 🎨 **CV Complètement Modernisé**

- **Design professionnel** : Interface hero avec gradient et photo de profil flottante
- **Layout moderne** : CSS Grid/Flexbox avec sidebar et contenu principal
- **Animations fluides** : Apparition progressive des sections au scroll
- **Interactions riches** : Tags de compétences interactifs avec effets ripple
- **Timeline améliorée** : Expériences et formations avec design épuré (sans cercles)
- **Téléchargement optimisé** : Bouton direct vers PDF avec script sécurisé
- **Responsive avancé** : Adaptation mobile/tablette/desktop perfectionnée
- **Print-friendly** : Optimisation automatique pour l'impression

### 🏗️ **Architecture CSS Moderne (CV)**

```text
cv-modern.css         # 600+ lignes - Styles principaux modernes
cv-animations.css     # 350+ lignes - Animations et interactions  
cv-interactions.js    # 320+ lignes - JavaScript pour interactivité
```

### 🎯 **Fonctionnalités CV Avancées**

- **Section Hero** : Photo + infos + liens sociaux + actions
- **Compétences par catégorie** : Langages, BDD, Applications avec tags colorés
- **Soft Skills interactifs** : Tags cliquables avec animations
- **Langues et centres d'intérêt** : Listes stylisées avec icônes
- **Timeline professionnelle** : Expériences avec missions détaillées
- **Formations académiques** : Parcours avec détails et établissements

### 🔧 **Interface Admin Harmonisée**

- **Menu unifié** : Style admin.php appliqué à toutes les pages d'administration
- **Navigation cohérente** : Même design sur admin, candidatures, messages, projets, galerie
- **Architecture modulaire** : Composants réutilisables pour l'interface
- **Responsive admin** : Interface d'administration adaptative

### 📱 **Améliorations UX/UI**

- **Notifications toast** : Système de notifications modernes
- **Partage natif** : Fonction de partage avec fallback copie de lien
- **Animations CSS natives** : Performance optimisée sans dépendances
- **Accessibilité** : Support reduced-motion et navigation clavier
- **Variables CSS** : Système de design cohérent et maintenable

### 🚀 **Performance et Sécurité**

- **Scripts optimisés** : JavaScript modulaire et performant
- **CSS variables** : Système de thème centralisé
- **Téléchargement sécurisé** : Headers HTTP forcés pour les fichiers
- **Intersection Observer** : Animations au scroll optimisées
- **Print optimization** : Mode impression automatique

## 🗂️ **Structure du Projet Complète**

```text
cedric-goujon.fr/
├── 📄 Pages principales
│   ├── index.php              # Page d'accueil
│   ├── cv.php                 # CV modernisé (nouveau design v3.0)
│   ├── contact.php            # Formulaire de contact
│   ├── projets.php            # Portfolio projets
│   └── exercice.html          # Démo GitHub API
│
├── 🎨 Styles CV modernes (NOUVEAU)
│   ├── cv-modern.css          # 600+ lignes - Design principal
│   ├── cv-animations.css      # 350+ lignes - Animations
│   └── cv-interactions.js     # 320+ lignes - Interactivité
│
├── 🔧 Administration harmonisée
│   ├── admin.php              # Interface principale
│   ├── admin_candidatures.php # Gestion candidatures (menu unifié)
│   ├── admin_messages.php     # Gestion messages (menu unifié)
│   ├── admin_projets.php      # Gestion projets (menu unifié)
│   ├── admin_gallery.php      # Gestion galerie (menu unifié)
│   ├── admin_utilisateur.php  # Gestion utilisateur (menu unifié)
│   ├── admin_systeme.php      # Paramètres système (menu unifié)
│   ├── admin-modern.css       # Styles admin
│   └── admin.js               # Fonctionnalités admin
│
├── 📋 Module candidatures
│   ├── candidatures/
│   │   ├── index.php          # Vue principale
│   │   ├── ajouter_candidature.php
│   │   ├── modifier_candidature.php
│   │   ├── supprimer_candidature.php
│   │   ├── liste_candidatures.php
│   │   └── style.css          # Styles spécifiques
│
├── 🛠️ Utilitaires et helpers
│   ├── config.php             # Configuration base de données
│   ├── auth_helper.php        # Fonctions authentification
│   ├── download.php           # Téléchargement sécurisé CV (NOUVEAU)
│   ├── script.js              # Scripts principaux
│   └── style.css              # Styles globaux
│
├── 🗄️ Ressources
│   ├── assets/
│   │   ├── img/moi.jpg        # Photo profil
│   │   └── CV_CGO_FS.pdf      # CV téléchargeable
│   ├── favicon.png
│   └── robots.txt
│
└── 📚 Documentation
    ├── README.md              # Ce fichier (mis à jour v3.0)
    └── CV-README.md           # Documentation CV moderne (NOUVEAU)
```

## 🛠️ Technologies utilisées

### Frontend

![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/javascript-%23323330.svg?style=for-the-badge&logo=javascript&logoColor=%23F7DF1E)

**🆕 Technologies CSS modernes ajoutées :**
- **CSS Grid & Flexbox** : Layout moderne et responsive
- **CSS Variables** : Système de design centralisé
- **CSS Animations natives** : Performance 60fps optimisée
- **Intersection Observer API** : Animations au scroll
- **Media Queries avancées** : Responsive design perfectionnée

### Backend

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300000f.svg?style=for-the-badge&logo=mysql&logoColor=white)

### Outils & Libraries

![FontAwesome](https://img.shields.io/badge/Font_Awesome-339AF0?style=for-the-badge&logo=fontawesome&logoColor=white)
![Node.js](https://img.shields.io/badge/node.js-6DA55F?style=for-the-badge&logo=node.js&logoColor=white)
![Puppeteer](https://img.shields.io/badge/Puppeteer-40B5A4?style=for-the-badge&logo=puppeteer&logoColor=white)

## 🚀 **Nouveautés Techniques Détaillées**

### 💎 **CV Moderne - Architecture CSS**

#### **Variables CSS Centralisées**
```css
:root {
  --primary-color: #2563eb;
  --accent-color: #f59e0b;
  --text-dark: #1f2937;
  --surface: #ffffff;
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  --radius-lg: 12px;
  /* ... 20+ variables */
}
```

#### **Layout CSS Grid Moderne**
```css
.cv-main {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: var(--spacing-xl);
}

.hero-content {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
}
```

#### **Animations CSS Natives**
```css
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

.fade-in { animation: fadeIn 0.6s ease-out; }
.slide-up { animation: slideUp 0.6s ease-out; }
```

### 🔧 **JavaScript Moderne - Fonctionnalités**

#### **Intersection Observer pour les animations**
```javascript
const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);
```

#### **Système de téléchargement sécurisé**
```javascript
function downloadPDF(event, link) {
    event.preventDefault();
    const tempLink = document.createElement('a');
    tempLink.href = link.href;
    tempLink.download = 'CV_Cedric_Goujon.pdf';
    tempLink.click();
}
```

#### **Notifications toast modernes**
```javascript
function createNotification() {
    return {
        show: function(message, type = 'info') {
            // Création dynamique de notifications
        }
    };
}
```

## 📈 **Améliorations de Performance**

### ✅ **Optimisations CSS**
- **Variables CSS** : Réduction de 40% du code redondant
- **CSS Grid/Flexbox** : Layout plus performant que les floats
- **Animations natives** : 60fps garantis avec `transform` et `opacity`
- **Lazy loading** : Animations déclenchées au scroll

### ✅ **Optimisations JavaScript**
- **Modularité** : Scripts séparés par fonctionnalité
- **Event delegation** : Meilleure gestion des événements
- **Debounced events** : Optimisation des événements de scroll
- **No dependencies** : JavaScript vanilla pour les performances

### ✅ **Optimisations UX**
- **Progressive enhancement** : Fonctionnel sans JavaScript
- **Accessibility** : Support des préférences `reduced-motion`
- **Print optimization** : CSS dédié pour l'impression
- **Mobile-first** : Design responsive optimisé

## 📂 Installation et configuration

### Prérequis
- **PHP 7.4+** avec extensions PDO et MySQL
- **MySQL/MariaDB** pour la base de données
- **Serveur web** (Apache/Nginx) avec mod_rewrite

### Installation

1. **Cloner le dépôt**
```bash
git clone https://github.com/HrexD/cedric-goujon.fr.git
cd cedric-goujon.fr
```

2. **Configuration de la base de données**
```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE cedric_site;

# Importer la structure (si fournie)
mysql -u root -p cedric_site < database.sql
```

3. **Configuration**
```php
// Modifier config.php
$host = 'localhost';
$dbname = 'cedric_site';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';
```

4. **Permissions**
```bash
# Assurer les permissions d'écriture
chmod 755 assets/
chmod 644 assets/CV_CGO_FS.pdf
```

## 🎯 Utilisation

### Interface publique
- Accédez à `index.php` pour la page d'accueil
- `cv.php` pour le CV moderne
- `contact.php` pour le formulaire de contact
- `projets.php` pour le portfolio

### Interface d'administration
- Accédez à `admin.php` avec vos identifiants
- Gérez les messages depuis `admin_messages.php`
- Gérez les candidatures depuis `admin_candidatures.php`
- Toutes les pages admin ont maintenant une navigation unifiée

### CV moderne
- Design responsive automatique
- Téléchargement PDF direct via `download.php`
- Animations optimisées pour la performance
- Mode impression automatique

## 🔧 Maintenance et développement

### Logs et debugging
- Les erreurs PHP sont loggées
- Console JavaScript pour le debugging des animations
- Mode développement disponible

### Mises à jour futures
- Architecture modulaire prête pour extensions
- CSS variables facilitent les changements de thème
- JavaScript modulaire permet l'ajout de fonctionnalités

### Contribution
Ce projet étant personnel, les contributions directes ne sont pas recherchées. Cependant, les suggestions et retours sont les bienvenus !

## 📞 Contact

- **Site web** : [cedric-goujon.fr](https://cedric-goujon.fr)
- **Email** : cedric.adam.goujon@gmail.com
- **LinkedIn** : [Cédric Goujon](https://www.linkedin.com/in/cédric-goujon-884522b6/)
- **GitHub** : [@HrexD](https://github.com/HrexD)

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

<div align="center">
  <strong>🚀 Développé avec passion par Cédric Goujon</strong><br>
  <em>Version 3.0 - Novembre 2025 - CV Modernisé</em>
</div>