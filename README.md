# UPF Gestion - Application de Gestion Universitaire

## 📋 Description

Application web complète développée en PHP procédural pour la gestion des étudiants, filières, utilisateurs et documents à l'Université Privée de Fès (UPF). L'application offre deux espaces distincts : un espace administrateur pour la gestion complète et un espace étudiant pour consulter les informations personnelles.

## ✨ Fonctionnalités Principales

### 🔐 Authentification et Sécurité
- Système d'authentification sécurisé avec rôles (Administrateur / Étudiant)
- Réinitialisation de mot de passe par email
- Gestion des sessions et protection CSRF
- Cookies pour "Se souvenir de moi"

### 👨‍💼 Espace Administrateur
- **Tableau de bord** avec statistiques en temps réel
- **Gestion des Étudiants** : Ajout, modification, suppression, recherche
- **Gestion des Filières** : CRUD complet
- **Gestion des Utilisateurs** : Administration des comptes
- **Upload de photos** et documents PDF
- **Rapports d'absences**

### 👨‍🎓 Espace Étudiant
- Consultation du profil personnel
- Accès aux notes et résultats
- Téléchargement de documents
- Changement de mot de passe
- Historique des absences

### 📊 Fonctionnalités Avancées
- Interface responsive et moderne
- Notifications par email
- Système de logs pour traçabilité
- Validation côté client et serveur
- Protection contre les injections SQL

## 🛠️ Technologies Utilisées

- **Backend** : PHP 7.4+ (procédural)
- **Base de données** : MySQL 5.7+
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **UI/UX** : Font Awesome, Google Fonts, Animations CSS
- **Email** : PHPMailer
- **Serveur** : Apache (XAMPP/WAMP recommandé)

## 📋 Prérequis

Avant d'installer l'application, assurez-vous d'avoir :

- **PHP 7.4 ou supérieur** installé
- **MySQL 5.7 ou supérieur**
- **Serveur web** (Apache recommandé)
- **Navigateur web** moderne (Chrome, Firefox, Edge)
- **Accès internet** pour les CDN (Font Awesome, Google Fonts)

### Environnements Recommandés
- **XAMPP** (Windows) - Inclut Apache, MySQL, PHP
- **WAMP** (Windows) - Alternative à XAMPP
- **MAMP** (Mac) - Pour utilisateurs Mac
- **LAMP** (Linux) - Apache, MySQL, PHP

## 🚀 Installation - Guide Complet (A à Z)

### Étape 1 : Téléchargement du Projet

#### Option A : Téléchargement Direct
1. Rendez-vous sur le dépôt GitHub : `https://github.com/radouane99/Gestion_UPF`
2. Cliquez sur **"Code"** > **"Download ZIP"**
3. Extrayez le fichier ZIP dans votre dossier `htdocs` (XAMPP) ou `www` (WAMP)
4. Renommez le dossier extrait en `Gestion_UPF`

#### Option B : Clonage Git (Recommandé)
```bash
# Naviguez vers le dossier htdocs
cd C:\xampp\htdocs

# Clonez le dépôt
git clone https://github.com/radouane99/Gestion_UPF.git

# Accédez au dossier
cd Gestion_UPF
```

### Étape 2 : Configuration de la Base de Données

#### A. Création de la Base de Données
1. Ouvrez **phpMyAdmin** (http://localhost/phpmyadmin)
2. Cliquez sur **"Nouvelle base de données"**
3. Nommez-la : `gestion_upf_db`
4. Sélectionnez **"utf8_general_ci"** comme collation
5. Cliquez sur **"Créer"**

#### B. Importation des Tables
1. Dans phpMyAdmin, sélectionnez la base `gestion_upf_db`
2. Cliquez sur **"Importer"**
3. Choisissez le fichier : `Base de données/gestion_upf_db.sql`
4. Cliquez sur **"Exécuter"**

#### C. Vérification de l'Importation
Vérifiez que les tables suivantes ont été créées :
- `etudiants`
- `filieres`
- `utilisateurs`
- `documents`
- `absences`
- `logs`

### Étape 3 : Configuration de l'Application

#### A. Configuration de la Base de Données
1. Ouvrez le fichier `config/database.php`
2. Modifiez les paramètres de connexion :

```php
<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_upf_db');
define('DB_USER', 'root');        // Par défaut dans XAMPP
define('DB_PASS', '');            // Mot de passe vide par défaut
define('DB_CHARSET', 'utf8');
?>
```

#### B. Configuration Email (Optionnel mais Recommandé)
1. Ouvrez le fichier `includes/EmailService.php`
2. Configurez les paramètres SMTP :

```php
private $smtpHost = 'smtp.gmail.com';    // Ou votre fournisseur
private $smtpPort = 587;
private $smtpUser = 'votre-email@gmail.com';
private $smtpPass = 'votre-mot-de-passe-app';
```

**Note** : Pour Gmail, activez l'authentification à 2 facteurs et générez un mot de passe d'application.

#### C. Permissions des Dossiers
Assurez-vous que les dossiers suivants sont accessibles en écriture :
- `uploads/photos/`
- `uploads/documents/`
- `logs/`

Sur Windows/XAMPP, ces permissions sont généralement déjà correctes.

### Étape 4 : Démarrage des Services

#### A. Démarrage XAMPP
1. Ouvrez le **Panneau de Contrôle XAMPP**
2. Démarrez **Apache**
3. Démarrez **MySQL**
4. Vérifiez que les services sont en vert

#### B. Test de l'Installation
1. Ouvrez votre navigateur
2. Accédez à : `http://localhost/Gestion_UPF`
3. Vous devriez voir la page de connexion

### Étape 5 : Configuration Initiale (Première Utilisation)

#### A. Connexion Administrateur
- **Login** : `admin`
- **Mot de passe** : `admin123`

#### B. Vérifications Post-Installation
1. **Test de connexion** avec les comptes par défaut
2. **Upload de test** : Essayez d'uploader une photo
3. **Ajout d'étudiant** : Testez les fonctionnalités CRUD
4. **Email** : Testez la réinitialisation de mot de passe

## 👥 Comptes par Défaut

### Administrateur
- **Login** : `admin`
- **Mot de passe** : `admin123`
- **Rôle** : Accès complet à toutes les fonctionnalités

### Étudiant de Test
- **Login** : `ahmed.alaoui`
- **Mot de passe** : `password123`
- **Rôle** : Accès limité à l'espace étudiant

## 📁 Structure du Projet

```
Gestion_UPF/
├── index.php                 # Page d'accueil
├── install.php              # Script d'installation
├── README.MD                # Documentation
├── assets/
│   └── style.css           # Styles CSS globaux
├── auth/                   # Système d'authentification
│   ├── login.php
│   ├── login_traitement.php
│   ├── logout.php
│   └── forgot_password.php
├── admin/                  # Espace administrateur
│   ├── dashboard.php
│   ├── etudiants/
│   ├── filieres/
│   └── utilisateurs/
├── user/                   # Espace étudiant
│   ├── profil.php
│   ├── notes.php
│   ├── documents.php
│   └── changer_password.php
├── absences/               # Gestion des absences
├── config/                 # Configuration
│   └── database.php
├── includes/               # Fichiers communs
│   ├── header.php
│   ├── footer.php
│   ├── functions.php
│   └── EmailService.php
├── uploads/                # Fichiers uploadés
│   ├── photos/
│   └── documents/
├── logs/                   # Fichiers de logs
├── Base de données/        # Scripts SQL
└── PHPMailer/             # Bibliothèque email
```

## 🎯 Utilisation de l'Application

### Pour les Administrateurs
1. **Connexion** : Utilisez les identifiants admin
2. **Tableau de bord** : Vue d'ensemble des statistiques
3. **Gestion étudiants** : Ajouter/modifier/supprimer des étudiants
4. **Gestion filières** : Administrer les filières
5. **Documents** : Uploader et gérer les documents PDF

### Pour les Étudiants
1. **Connexion** : Utilisez vos identifiants personnels
2. **Profil** : Consulter et modifier vos informations
3. **Notes** : Voir vos résultats académiques
4. **Documents** : Télécharger vos documents
5. **Absences** : Consulter votre historique

## 🔧 Dépannage

### Problèmes Courants

#### Erreur de Connexion Base de Données
- Vérifiez les paramètres dans `config/database.php`
- Assurez-vous que MySQL est démarré
- Vérifiez que la base `gestion_upf_db` existe

#### Page Blanche
- Vérifiez les logs PHP (`logs/error.log`)
- Activez l'affichage des erreurs dans `php.ini`
- Vérifiez les permissions des fichiers

#### Upload Impossible
- Vérifiez les permissions du dossier `uploads/`
- Vérifiez la taille maximale d'upload dans `php.ini`
- Vérifiez le type de fichier autorisé

#### Email Non Envoyé
- Vérifiez la configuration SMTP
- Pour Gmail : Utilisez un mot de passe d'application
- Vérifiez les logs email

### Commandes Utiles

```bash
# Vérifier la version PHP
php --version

# Redémarrer Apache (Windows)
net stop apache2.4 && net start apache2.4

# Redémarrer MySQL (Windows)
net stop mysql && net start mysql
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

### Guidelines de Développement
- Respectez la structure MVC légère
- Commentez votre code en français
- Testez toutes les fonctionnalités avant commit
- Suivez les standards PSR-12 pour PHP

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 📞 Support

Pour obtenir de l'aide :
- Ouvrez une **issue** sur GitHub
- Contactez l'équipe de développement
- Consultez la documentation complète

## 🔄 Mises à Jour

### Version 1.0.0
- ✅ Système d'authentification complet
- ✅ Gestion CRUD étudiants et filières
- ✅ Upload de documents et photos
- ✅ Interface responsive
- ✅ Réinitialisation de mot de passe
- ✅ Système de logs

---

**Développé avec ❤️ pour l'Université Privée de Fès**
>>>>>>> 806cdd3 (Create README.md for UPF Gestion application)
