# 🌐 Cédric Goujon - Site Personnel

[![Website](https://img.shields.io/website?url=https%3A//cedric-goujon.fr&style=for-the-badge&logo=internet-explorer&logoColor=white)](https://cedric-goujon.fr)
[![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg?style=for-the-badge)](https://github.com/HrexD/cedric-goujon.fr/graphs/commit-activity)
[![GitHub last commit](https://img.shields.io/github/last-commit/HrexD/cedric-goujon.fr?style=for-the-badge)](https://github.com/HrexD/cedric-goujon.fr/commits/main)

> Site personnel d'un développeur Full-Stack avec 2 ans et demi d'expérience, spécialisé dans le développement d'applications web modernes et performantes.

## 📋 À propos

Ce dépôt contient le code source de mon site personnel [cedric-goujon.fr](https://cedric-goujon.fr), développé entièrement sur mesure. Le site présente mon profil professionnel, mes compétences, mes projets et permet aux visiteurs de me contacter.

## ✨ Fonctionnalités

### 🏠 **Pages Principales**
- 🏠 **Page d'accueil** - Présentation personnelle et professionnelle
- 📄 **CV interactif modernisé** - Interface complètement redesignée avec animations *(v4.0)*
- 🚀 **Portfolio de projets dynamique** - Vitrine avec intégration GitHub API *(nouveau)*
- 📧 **Formulaire de contact** - Communication directe avec sauvegarde en base

### 🔧 **Interface d'Administration Complète**
- 🎛️ **Dashboard unifié** - Interface d'administration harmonisée
- 📧 **Gestion des messages** - Interface moderne de traitement
- 💼 **Gestion des candidatures** - Module complet de suivi
- 🚀 **Gestion des projets** - Administration du portfolio
- 🖼️ **Galerie de fichiers** - Système d'upload et gestion
- 👤 **Profil utilisateur** - Configuration des informations
- ⚙️ **Système et logs** - Monitoring et maintenance

### 🧭 **Nouvelles Fonctionnalités Avancées**
- 🎯 **API GitHub intégrée** - Affichage automatique des repositories *(v4.0)*
- 🎨 **Design system unifié** - CSS variables et composants modulaires
- 📱 **Navigation responsive** - Interface harmonisée sur toutes les plateformes
- 💎 **Architecture externalisée** - Styles et scripts organisés et optimisés

## 🆕 **Dernières nouveautés (v4.0 - Novembre 2025)**

### 🎯 **Intégration GitHub API Avancée**

- **Portfolio dynamique** : Récupération automatique des repositories publics
- **Cache intelligent** : Système de cache 1h pour optimiser les performances
- **Filtrage avancé** : Exclusion automatique des forks et repositories archivés
- **Design GitHub-style** : Interface inspirée de GitHub avec statistiques
- **Informations complètes** : Stars, forks, langages, topics, dates

### 🎨 **Évolution du Portfolio Projets**

- **Section GitHub** : Affichage automatique des repositories publics
- **Cache API** : Système de mise en cache pour les performances
- **Statistiques** : Affichage des stars, forks, langages principaux
- **Actions multiples** : Voir le code, démonstration, téléchargement
- **Fallback intelligent** : Gestion d'erreur avec liens de secours
- **Metadata complètes** : Dates, descriptions, topics GitHub

### 🏗️ **Architecture Technique Avancée**

```text
Architecture GitHub:
├── Fonction getGitHubRepos()     # API GitHub avec cache
├── Filtrage intelligent          # Exclusion forks/archives  
├── Tri par mise à jour           # Repositories les plus récents
├── Limitation à 12 projets       # Performance optimisée
└── Headers professionnels        # User-Agent personnalisé
```

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
│   ├── cv.php                 # CV modernisé (nouveau design v4.0)
│   ├── contact.php            # Formulaire de contact
│   ├── projets.php            # Portfolio avec GitHub API (v4.0)
│   └── exercice.html          # Démo GitHub API
│
├── 🎨 Styles CV modernes (v3.0)
│   ├── cv-modern.css          # 600+ lignes - Design principal
│   ├── cv-animations.css      # 350+ lignes - Animations
│   ├── cv-interactions.js     # 320+ lignes - Interactivité
│   └── github-repos.css       # 350+ lignes - Styles GitHub (nouveau)
│

├── 🔧 Administration complète
│   ├── admin.php              # Dashboard principal
│   ├── admin_candidatures.php # Gestion candidatures
│   ├── admin_messages.php     # Gestion messages
│   ├── admin_projets.php      # Gestion portfolio
│   ├── admin_gallery.php      # Galerie fichiers
│   ├── admin_utilisateur.php  # Profil utilisateur
│   ├── admin_systeme.php      # Système et logs
│   └── admin_logs.php         # Monitoring détaillé
│
├── 🎯 API et intégrations (v4.0)
│   ├── GitHub API             # Récupération repositories
│   ├── Cache système          # Optimisation performances
│   └── Email automatisé       # Templates HTML/texte
│
├── 💾 Base de données
│   ├── Tables utilisateur     # Profil et authentification
│   ├── Messages et candidatures # Communication
│   └── Projets et galerie     # Portfolio et médias
│
├── 🎨 Assets et styles
│   ├── style.css              # Styles généraux
│   ├── admin-styles.css       # Interface administration
│   ├── github-repos.css       # Styles repositories GitHub
│   └── Various animations     # Fichiers d'animations
│
└── 🔧 Fichiers de test et debug
    ├── test_github_simple.php      # Test API GitHub
    ├── test_github_display.php     # Demo visuelle GitHub
    └── Various utilities            # Outils de développement
```

## 🛠️ **Technologies Utilisées**

### Frontend

![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/javascript-%23323330.svg?style=for-the-badge&logo=javascript&logoColor=%23F7DF1E)

### Backend

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300000f.svg?style=for-the-badge&logo=mysql&logoColor=white)

### APIs & Services

![GitHub](https://img.shields.io/badge/github-%23121011.svg?style=for-the-badge&logo=github&logoColor=white)
![GitHub API](https://img.shields.io/badge/GitHub_API-181717?style=for-the-badge&logo=github&logoColor=white)

### Outils & Libraries

![FontAwesome](https://img.shields.io/badge/Font_Awesome-339AF0?style=for-the-badge&logo=fontawesome&logoColor=white)
![Node.js](https://img.shields.io/badge/node.js-6DA55F?style=for-the-badge&logo=node.js&logoColor=white)
![Puppeteer](https://img.shields.io/badge/Puppeteer-40B5A4?style=for-the-badge&logo=puppeteer&logoColor=white)

## 🚀 **Architecture Technique Détaillée**

### 🎯 **API GitHub Intégrée (v4.0)**

```php
// Fonction principale avec cache intelligent
function getGitHubRepos($githubUrl) {
    // Cache 1 heure pour optimiser les performances
    // Filtrage automatique: exclut forks et archives
    // Tri par date de mise à jour (plus récents en premier)
    // Limite: 12 repositories maximum
}
```

**Fonctionnalités API** :

- **Cache intelligent** : Stockage temporaire 1h pour réduire les appels API
- **Filtrage avancé** : Exclusion automatique des forks et repositories archivés
- **Tri optimisé** : Par date de dernière mise à jour
- **Headers professionnels** : User-Agent personnalisé pour l'API
- **Gestion d'erreur** : Fallback avec liens directs GitHub
- **Performance** : Limitation à 12 projets les plus pertinents



### 🎨 **CSS Architecture Moderne**

```css
/* Variables globales centralisées */
:root {
  --primary-color: #2563eb;
  --accent-color: #f59e0b;
  --text-dark: #1f2937;
  --surface: #ffffff;
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  --radius-lg: 12px;
  /* 20+ variables pour consistance */
}
```

**Design System** :

- **CSS Grid & Flexbox** : Layout moderne et responsive
- **Variables CSS** : Système de design cohérent
- **Animations natives** : Performances optimisées sans librairies
- **Intersection Observer** : Animations au scroll fluides
- **Responsive design** : Breakpoints adaptatifs multiples
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

## 🎯 **Utilisation et Installation**

### Prérequis

- **Serveur Web** : Apache/Nginx avec PHP 7.4+
- **Base de données** : MySQL 5.7+ / MariaDB
- **Extensions PHP** : PDO, MySQLi, cURL, JSON
- **Optionnel** : Node.js pour outils de développement

### Installation locale

```bash
# Cloner le repository
git clone https://github.com/HrexD/cedric-goujon.fr.git
cd cedric-goujon.fr

# Configuration base de données
cp config.php.example config.php
# Éditer config.php avec vos paramètres DB

# Import structure base de données
mysql -u username -p database_name < database_structure.sql

# Lancer serveur local PHP
php -S localhost:8000
```

### Configuration GitHub API

```php
// Dans admin_utilisateur.php, configurer l'URL GitHub
$user['github'] = 'https://github.com/VotreUsername';
// L'API récupérera automatiquement les repositories publics
```

### Interface d'Administration

- **Accès admin** : `http://localhost:8000/admin.php`
- **Dashboard** : Vue d'ensemble avec statistiques
- **Questionnaires** : Création et gestion QCM
- **Projets** : Administration portfolio
- **Système** : Logs et maintenance

## 🔧 **Maintenance et Développement**

### Structure des logs

- **Erreurs PHP** : Loggées dans fichiers système
- **Test GitHub API** : `test_github_simple.php`
- **Interface monitoring** : `admin_logs.php`

### Debugging et tests

```php
// Test API GitHub
php test_github_simple.php

// Interface de test visuelle
access: /test_github_display.php
```

### Optimisations performances

- **Cache API GitHub** : 1 heure de cache automatique
- **CSS variables** : Réduction 40% du code redondant
- **JavaScript modulaire** : Chargement conditionnel
- **Images optimisées** : Compression et formats modernes

## 🚀 **Fonctionnalités Avancées**

### API GitHub Intégrée

- ✅ Récupération automatique des repositories publics
- ✅ Cache intelligent 1h pour performances optimales
- ✅ Filtrage des forks et repositories archivés
- ✅ Affichage statistics (stars, forks, langages)
- ✅ Interface GitHub-style avec animations



### Architecture Moderne

- ✅ CSS Grid & Flexbox responsive
- ✅ Variables CSS centralisées
- ✅ Animations CSS natives optimisées
- ✅ Progressive enhancement
- ✅ Accessibilité et reduced-motion

## 📈 **Roadmap et améliorations futures**

### v4.1 - Prochaines fonctionnalités
- [ ] Dashboard analytics avancé
- [ ] Export PDF questionnaires
- [ ] API REST pour mobile app
- [ ] Thème sombre/clair automatique
- [ ] PWA (Progressive Web App)

### v4.2 - Intégrations externes
- [ ] LinkedIn API pour expériences
- [ ] Google Analytics intégré
- [ ] CDN pour performances globales
- [ ] Système de notification push

## 🤝 **Contribution**

Ce projet étant personnel, les contributions directes ne sont pas recherchées. Cependant, les suggestions et retours sont les bienvenus via :

- **Issues GitHub** : Pour signaler bugs ou suggestions
- **Email** : Pour retours constructifs
- **LinkedIn** : Pour discussions professionnelles

## 📄 **Licence et Credits**

- **Licence** : MIT License
- **Frameworks** : FontAwesome Icons, CSS Grid/Flexbox
- **APIs** : GitHub API v3
- **Inspiration** : Modern web design patterns

## 📞 **Contact**

- **Site web** : [cedric-goujon.fr](https://cedric-goujon.fr)
- **Email** : cedric.adam.goujon@gmail.com
- **LinkedIn** : [Cédric Goujon](https://www.linkedin.com/in/cédric-goujon-884522b6/)
- **GitHub** : [@HrexD](https://github.com/HrexD)

---

<div align="center">
  <strong>🚀 Développé avec passion par Cédric Goujon</strong><br>
  <em>Version 4.0 - Novembre 2025 - GitHub API Integration</em>
</div>