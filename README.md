# 💼 CV en ligne – Cédric Goujon

Ce projet est un site web de type **CV interactif** développé en **PHP**, basé sur un fichier `data.json` et stylisé avec **CSS personnalisé** (thème sombre/clair avec switcher intégré).

---

## 🚀 Fonctionnalités

- Affichage dynamique des informations depuis un fichier JSON (`data.json`)
- Thème sombre activé par défaut + 🌞 Switch vers un thème clair
- Responsive (compatible mobile et tablette)
- Design sobre, moderne et typé “développeur”
- Sans base de données – 100 % statique + PHP simple

---

## 📁 Structure du projet

```markdown

├── index.php          → Page principale du CV (template PHP)
├── data.json          → Données personnelles (nom, expériences, compétences…)
├── style.css          → Feuille de style avec thème clair/sombre
└── README.md          → Ce fichier
```

---

## ⚙️ Installation & usage local

1. Clone ou télécharge ce dépôt :

   ```shell
   git clone https://github.com/HrexD/cedric-goujon.fr
   cd cedric-goujon.fr
   ```

2. Lance un serveur local avec PHP :

   ```shell
   php -S localhost:8000
   ```

3. Ouvre ton navigateur :

    ```shell
   http://localhost:8000
   ```

---

## 🧠 Personnalisation

- **Contenu** : modifie le fichier `data.json`
- **Couleurs & typographie** : via `style.css` (`:root`)
- **Dépôts GitHub affichés** :
  - par défaut, tous les repos sont récupérés via :  
    `https://api.github.com/users/<utilisateur>/repos`
  - tu peux filtrer, trier, ou limiter le nombre dans le JavaScript

---

## 🔧 GitHub Projects Integration (JS)

Les projets sont automatiquement affichés depuis ton GitHub avec un rendu sous forme de “cards” :

- Titre + lien vers le dépôt
- Description du projet
- Design cohérent avec ton thème

Tu peux personnaliser le JS dans le fichier `index.php` :

```
fetch("https://api.github.com/users/HrexD/repos")
  .then(res => res.json())
  .then(repos => {
    // Génération des cards ici...
  });
```

---

## 👨‍💻 Auteur

- **Cédric Goujon**
- [LinkedIn](https://www.linkedin.com/in/c%C3%A9dric-goujon-884522b6/)
- [GitHub](https://github.com/HrexD)

---

## 📄 Licence

Ce projet est open-source et libre d’utilisation pour un usage personnel ou professionnel.
