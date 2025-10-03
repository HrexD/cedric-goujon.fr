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
- 📄 **CV interactif** - Parcours, compétences et expériences
- 🚀 **Portfolio de projets** - Vitrine de mes réalisations
- 📧 **Formulaire de contact** - Communication directe avec sauvegarde en base
- 🔧 **Interface d'administration** - Gestion des messages reçus
- 🌓 **Mode sombre/clair** - Basculement de thème
- 📱 **Design responsive** - Compatible tous appareils
- 🔍 **Exercice GitHub** - Recherche d'utilisateurs GitHub (démo API)

## 🛠️ Technologies utilisées

### Frontend
![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/javascript-%23323330.svg?style=for-the-badge&logo=javascript&logoColor=%23F7DF1E)

### Backend
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300000f.svg?style=for-the-badge&logo=mysql&logoColor=white)

### Outils & Libraries
![FontAwesome](https://img.shields.io/badge/Font_Awesome-339AF0?style=for-the-badge&logo=fontawesome&logoColor=white)
![Node.js](https://img.shields.io/badge/node.js-6DA55F?style=for-the-badge&logo=node.js&logoColor=white)
![Puppeteer](https://img.shields.io/badge/Puppeteer-40B5A4?style=for-the-badge&logo=puppeteer&logoColor=white)

## 📁 Structure du projet

```
├── 📄 index.php          # Page d'accueil
├── 📄 cv.php             # Page CV
├── 📄 projets.php        # Page projets
├── 📄 contact.php        # Page contact
├── 📄 exercice.html      # Démo recherche GitHub
├── 📄 config.php         # Configuration base de données
├── 📄 data.json          # Données personnelles (CV)
├── 🎨 style.css          # Styles principaux
├── ⚡ script.js          # Scripts principaux
├── ⚡ exo.js             # Script exercice GitHub
├── 🖼️ assets/img/        # Images
├── 🔧 .htaccess          # Configuration Apache
├── 🤖 robots.txt         # Instructions robots
└── 📦 package.json       # Dépendances Node.js
```

## 🚀 Installation & Déploiement

### Prérequis
- PHP 7.4+
- MySQL 5.7+
- Serveur web (Apache/Nginx)
- Node.js (pour les dépendances de développement)

### Installation locale

1. **Cloner le projet**
   ```bash
   git clone https://github.com/HrexD/cedric-goujon.fr.git
   cd cedric-goujon.fr
   ```

2. **Installer les dépendances**
   ```bash
   npm install
   ```

3. **Configuration base de données**
   - Créer une base MySQL
   - Importer la structure nécessaire
   - Modifier `config.php` avec vos identifiants

4. **Configuration serveur**
   - Pointer le document root vers le dossier du projet
   - S'assurer que les réécritures d'URL sont activées

### Variables d'environnement

Modifier `config.php` avec vos paramètres :
```php
$host = 'localhost';        // Hôte MySQL
$db   = 'votre_bdd';       // Nom de la base
$user = 'votre_user';      // Utilisateur MySQL
$pass = 'votre_password';  // Mot de passe
```

## 🔗 API & Intégrations

- **API GitHub** - Recherche d'utilisateurs (exercice.html)
- **Base de données MySQL** - Stockage des données personnelles et projets
- **Font Awesome** - Icônes
- **Google Fonts** - Typographies

## 🤝 Contribution

Ce projet étant personnel, les contributions ne sont pas activement recherchées. Cependant, les suggestions et retours sont les bienvenus !

1. Fork le projet
2. Créer une branche pour votre feature
3. Commit vos changements
4. Push vers la branche
5. Ouvrir une Pull Request

## 📞 Contact

- **Site web** : [cedric-goujon.fr](https://cedric-goujon.fr)
- **Email** : cedric.adam.goujon@gmail.com
- **LinkedIn** : [Cédric Goujon](https://www.linkedin.com/in/cédric-goujon-884522b6/)
- **GitHub** : [@HrexD](https://github.com/HrexD)

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

<div align="center">
  <strong>🚀 Développé avec passion par Cédric Goujon</strong>
</div>
