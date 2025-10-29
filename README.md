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
- 🔧 **Interface d'administration** - Gestion des messages reçus et système complet
- 📋 **Gestion des candidatures** - Module complet de suivi des candidatures *(nouveau)*
- 🧭 **Navigation dynamique** - Affichage conditionnel selon le statut admin *(nouveau)*
- � **Architecture CSS/JS externalisée** - Styles et scripts organisés *(nouveau)*
- �🌓 **Mode sombre/clair** - Basculement de thème
- 📱 **Design responsive** - Compatible tous appareils
- 🔍 **Exercice GitHub** - Recherche d'utilisateurs GitHub (démo API)

### 🆕 Nouvelles fonctionnalités (v2.0)

#### 📋 **Module Candidatures**
- **Interface complète** : Ajout, modification, suppression des candidatures
- **Suivi des statuts** : En attente, entretien, acceptée, refusée
- **Informations détaillées** : Données candidat, poste, entreprise, notes
- **Statistiques** : Vue d'ensemble des candidatures par statut
- **Accès sécurisé** : Réservé aux administrateurs connectés

#### 🧭 **Navigation Intelligente**
- **Affichage conditionnel** : Le lien "Candidatures" apparaît uniquement pour les admins
- **Navigation bidirectionnelle** : Accès depuis/vers toutes les pages
- **Chemins relatifs adaptés** : Gestion automatique des sous-dossiers
- **Styles distinctifs** : Boutons Admin et Candidatures avec design spécifique

#### 🏗️ **Architecture Améliorée**
- **CSS externalisé** : `admin.css` (500+ lignes) pour l'interface d'administration
- **JavaScript externalisé** : `admin.js` (400+ lignes) pour les fonctionnalités avancées
- **Fonctions d'authentification** : `auth_helper.php` pour la gestion centralisée
- **Documentation complète** : Guides de migration et d'utilisation

#### 🖼️ **Système d'Upload et Galerie**

- **Interface d'upload moderne** : Drag & drop avec prévisualisation temps réel
- **Upload séquentiel optimisé** : Traitement un par un pour maximiser la bande passante
- **Support multi-formats** : Images et vidéos avec validation automatique
- **Galerie d'administration** : Visualisation en grille et suppression sécurisée
- **Logging complet** : Traçabilité des uploads avec horodatage et métadonnées
- **Gestion des erreurs** : Reprise automatique et messages d'état détaillés

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

```text
├── 📄 index.php              # Page d'accueil
├── 📄 cv.php                 # Page CV
├── 📄 projets.php            # Page projets
├── 📄 contact.php            # Page contact
├── 📄 admin.php              # Interface d'administration
├── 📄 exercice.html          # Démo recherche GitHub
├── 📄 config.php             # Configuration base de données
├── 📄 auth_helper.php        # Fonctions d'authentification (nouveau)
├── 📄 upload.php             # Interface d'upload avec drag & drop (nouveau)
├── 📄 upload_handler.php     # Backend de traitement des uploads (nouveau)
├── 📄 admin_gallery.php      # Galerie d'administration des uploads (nouveau)
├── 📄 admin_delete_upload.php # Suppression sécurisée des fichiers (nouveau)
├── 📄 data.json              # Données personnelles (CV)
├── 🎨 style.css              # Styles principaux
├── 🎨 admin.css              # Styles administration (nouveau)
├── ⚡ script.js              # Scripts principaux
├── ⚡ admin.js               # Scripts administration (nouveau)
├── ⚡ exo.js                 # Script exercice GitHub
├── 🖼️ assets/img/            # Images
├── 📁 uploads/               # Dossier des fichiers uploadés (nouveau)
├── 📋 candidatures/          # Module candidatures (nouveau)
│   ├── index.php             # Liste et statistiques
│   ├── ajouter_candidature.php
│   ├── modifier_candidature.php
│   └── supprimer_candidature.php
├── 🔧 .htaccess              # Configuration Apache
├── 🤖 robots.txt             # Instructions robots
├── 📦 package.json           # Dépendances Node.js
```

## �️ Base de données

### Tables principales

- **`utilisateur_principal`** - Données personnelles du CV
- **`experiences_pro`** - Expériences professionnelles
- **`missions_experience`** - Missions détaillées par expérience
- **`formations`** - Formations et diplômes
- **`details_formation`** - Détails des formations
- **`projets`** - Portfolio de projets
- **`langues`** - Compétences linguistiques
- **`soft_skills`** - Compétences comportementales
- **`technologies`** - Stack technique
- **`interets`** - Centres d'intérêt
- **`contacts`** - Messages des visiteurs
- **`candidatures`** - Suivi des candidatures *(nouveau)*



## �🚀 Installation & Déploiement

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

5. **Configuration administration**
   - Modifier les identifiants admin dans `config.php`
   - Se connecter via `/admin.php` pour accéder au système complet

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
- **Système d'authentification** - Sessions sécurisées pour l'administration
- **Gestion des candidatures** - CRUD complet avec statuts *(nouveau)*
- **Navigation dynamique** - Affichage conditionnel selon les permissions *(nouveau)*
- **Font Awesome** - Icônes
- **Google Fonts** - Typographies

## 🆕 Nouveautés v2.0

### 🔐 Système d'authentification amélioré
- Sessions sécurisées avec `auth_helper.php`
- Navigation adaptée selon le statut utilisateur
- Redirection automatique pour les pages protégées

### 📋 Module candidatures complet
- Interface CRUD complète (Create, Read, Update, Delete)
- Statuts de suivi : en_attente, entretien, acceptée, refusée
- Statistiques et tableaux de bord

### 🖼️ Système d'upload et galerie
- Interface moderne avec drag & drop (`upload.php`)
- Upload séquentiel optimisé pour maximiser la bande passante
- Support images et vidéos avec prévisualisation temps réel
- Galerie d'administration intégrée (`admin_gallery.php`)
- Suppression sécurisée des fichiers avec authentification
- Logging complet dans `upload_log.csv` pour traçabilité
- Design responsive avec badges colorés

### 🏗️ Architecture modernisée
- CSS externalisé avec `admin.css` (500+ lignes)
- JavaScript externalisé avec `admin.js` (400+ lignes)
- Fonctions centralisées d'authentification
- Documentation technique complète

### 🎨 Améliorations UX/UI
- Styles distinctifs pour les liens admin/candidatures
- Animations et transitions fluides
- Modales et notifications améliorées
- Thème sombre/clair maintenu sur toutes les pages

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
