# 📦 Stock Management App - COFAT

<div align="center">

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**Application web complète pour la gestion des stocks et des inventaires**

[Démo](#-fonctionnalités) • [Installation](#-installation) • [Documentation](#-structure-du-projet) • [Contribution](#-contribution)

</div>

---

## 📋 Table des matières

- [À propos](#-à-propos)
- [Fonctionnalités](#-fonctionnalités)
- [Technologies](#-technologies)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Structure du projet](#-structure-du-projet)
- [Base de données](#-base-de-données)
- [Captures d'écran](#-captures-décran)
- [Roadmap](#-roadmap)
- [Contribution](#-contribution)
- [Licence](#-licence)
- [Auteur](#-auteur)

---

## 🎯 À propos

**Stock Management App** est une application web développée pour faciliter la gestion complète des stocks pour l'entreprise COFAT. Elle permet de gérer les articles, les fournisseurs, les employés, les catégories et de suivre l'état des stocks en temps réel avec des fonctionnalités d'export Excel.

### Objectifs du projet
- ✅ Simplifier la gestion quotidienne des stocks
- ✅ Centraliser les informations produits et fournisseurs
- ✅ Générer des rapports et exports automatisés
- ✅ Offrir une interface intuitive et responsive

---

## ✨ Fonctionnalités

### 🛒 Gestion des Articles
- ➕ Ajouter de nouveaux articles avec prix, quantité et catégorie
- ✏️ Modifier les informations des articles existants
- 🗑️ Supprimer des articles avec confirmation
- 🔍 Visualiser tous les articles dans un tableau interactif

### 📊 Gestion des Stocks
- 📦 Suivre les quantités en stock par emplacement
- 🏪 Gérer plusieurs emplacements (Entrepôt Principal, Magasin Tunis, Magasin Sfax...)
- ⚠️ Alertes pour les stocks bas
- 📍 Localisation précise des articles

### 👥 Gestion des Utilisateurs
- 👤 Gestion des employés et administrateurs
- 🔐 Système d'authentification sécurisé
- 👨‍💼 Rôles et permissions différenciés

### 🏢 Gestion des Fournisseurs
- 📇 Base de données complète des fournisseurs
- 📞 Informations de contact (téléphone, email, adresse)
- 🔗 Association articles-fournisseurs

### 🗂️ Gestion des Catégories
- 📑 Organisation des articles par catégories
- ➕ Création de catégories personnalisées
- 🏷️ Classification facile des produits

### 📈 Dashboard
- 📊 Vue d'ensemble des statistiques
- 📉 Graphiques et métriques clés
- 🔔 Alertes et notifications

### 📤 Export de données
- 📊 Export Excel (.xlsx) avec PhpSpreadsheet
- 📄 Export CSV pour compatibilité universelle
- 🎨 Mise en forme automatique des exports

---

## 🛠️ Technologies

### Backend
- **PHP 7.4+** - Langage serveur principal
- **MySQL 5.7+** - Base de données relationnelle
- **PDO** - Interface d'accès aux données sécurisée

### Frontend
- **HTML5 & CSS3** - Structure et style
- **Bootstrap 5.3** - Framework CSS responsive
- **JavaScript (ES6+)** - Interactivité côté client
- **SweetAlert2** - Popups et confirmations élégantes

### Bibliothèques
- **PhpSpreadsheet** - Génération de fichiers Excel
- **Composer** - Gestionnaire de dépendances PHP

### Serveur local
- **XAMPP / WAMP** - Environnement de développement local

---

## 📦 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- ✅ **PHP >= 7.4** ([Télécharger](https://www.php.net/downloads))
- ✅ **MySQL >= 5.7** ou **MariaDB >= 10.2**
- ✅ **Apache 2.4** (inclus dans XAMPP/WAMP)
- ✅ **Composer** ([Télécharger](https://getcomposer.org/download/))
- ✅ Un navigateur web moderne (Chrome, Firefox, Edge)

### Extensions PHP requises
markdown### Extensions PHP requises
```ini
extension=pdo_mysql
extension=mbstring
extension=zip
extension=gd
```

---

## 📥 Installation
```bash
# Cloner le projet
git clone https://github.com/votre-username/stock-management-cofat.git
cd stock-management-cofat

# Installer les dépendances
composer install

# Importer la base de données dans phpMyAdmin
# 1. Créer une base de données : cofat_stock
# 2. Importer le fichier : database/cofat_stock.sql
```

---

## ⚙️ Configuration

Modifier `config/database.php` :
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cofat_stock');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## 🚀 Utilisation

1. Démarrer XAMPP/WAMP
2. Accéder à : `http://localhost/stock-management-cofat`
3. Login par défaut : `admin@cofat.com` / `admin123`

---

## 📁 Structure du projet
```
stock-management-cofat/
├── config/          # Configuration
├── includes/        # Fichiers réutilisables
├── pages/           # Pages de l'application
├── assets/          # CSS, JS, images
├── database/        # Scripts SQL
└── vendor/          # Dépendances
```

---

## 🗄️ Base de données

**Tables principales :**
- `articles` - Produits en stock
- `fournisseurs` - Liste des fournisseurs
- `employes` - Utilisateurs
- `categories` - Catégories de produits
- `stock` - État des stocks

---

## 📸 Captures d'écran

*(Ajouter vos captures d'écran ici)*

---

## 🗺️ Roadmap

- [ ] API REST
- [ ] Application mobile
- [ ] Notifications par email
- [ ] Rapports avancés

---

## 🤝 Contribution

1. Fork le projet
2. Créer une branche : `git checkout -b feature/nouvelle-fonctionnalite`
3. Commit : `git commit -m 'Ajout nouvelle fonctionnalité'`
4. Push : `git push origin feature/nouvelle-fonctionnalite`
5. Ouvrir une Pull Request

---

## 📄 Licence

MIT License - voir [LICENSE](LICENSE)

---

## 👨‍💻 Auteur

**Votre Nom**
- GitHub: [@votre-username](https://github.com/votre-username)
- Email: votre.email@example.com

---

<div align="center">

⭐ **Star ce projet si vous l'aimez !** ⭐

</div>
