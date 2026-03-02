<?php
// includes/header.php - Version Moderne
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPF Gestion - <?php echo $title ?? 'Administration'; ?></title>
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="../assets/style.css">
    
    <style>
        /* ============================================= */
        /* VARIABLES GLOBALES */
        /* ============================================= */
        :root {
            --upf-blue: #294898;
            --upf-pink: #C72C82;
            --upf-gradient: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: var(--dark);
            line-height: 1.6;
        }

        /* ============================================= */
        /* HEADER MODERNE */
        /* ============================================= */
        .modern-header {
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 30px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Logo et titre */
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: var(--upf-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(41, 72, 152, 0.3);
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 700;
            background: var(--upf-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .logo-sub {
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 2px;
        }

        /* Menu de navigation */
        .header-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 30px;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            color: var(--gray);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .nav-link i {
            font-size: 1.2rem;
            transition: transform 0.3s;
        }

        .nav-link:hover {
            background: var(--light);
            color: var(--upf-blue);
        }

        .nav-link:hover i {
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: var(--upf-gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(199, 44, 130, 0.3);
        }

        /* Dropdown menu */
        .nav-item.dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 220px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 1000;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .nav-item.dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .dropdown-item i {
            width: 20px;
            color: var(--gray);
            font-size: 1rem;
        }

        .dropdown-item:hover {
            background: var(--light);
            color: var(--upf-pink);
        }

        .dropdown-item:hover i {
            color: var(--upf-pink);
        }

        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 8px;
        }

        /* Section droite du header */
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Recherche */
        .search-box {
            position: relative;
        }

        .search-box input {
            width: 280px;
            height: 45px;
            padding: 0 20px 0 45px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: white;
        }

        .search-box input:focus {
            width: 320px;
            border-color: var(--upf-pink);
            box-shadow: 0 0 0 3px rgba(199, 44, 130, 0.1);
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1rem;
        }

        /* Notifications */
        .notifications {
            position: relative;
        }

        .notifications-btn {
            width: 45px;
            height: 45px;
            border: none;
            background: var(--light);
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .notifications-btn:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .notifications-btn i {
            font-size: 1.2rem;
            color: var(--dark);
        }

        .notifications-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .notifications-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 1000;
        }

        .notifications:hover .notifications-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notifications-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notifications-header h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .notifications-header a {
            color: var(--upf-pink);
            text-decoration: none;
            font-size: 0.85rem;
        }

        .notifications-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: var(--light);
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            background: var(--light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--upf-pink);
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 3px;
        }

        .notification-time {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Profil utilisateur */
        .user-profile {
            position: relative;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 10px;
            background: var(--light);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .profile-btn:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            background: var(--upf-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .profile-info {
            text-align: left;
        }

        .profile-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark);
        }

        .profile-role {
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 240px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 1000;
        }

        .user-profile:hover .profile-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-header .avatar {
            width: 50px;
            height: 50px;
            background: var(--upf-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .profile-header .info h4 {
            font-size: 1rem;
            margin-bottom: 3px;
        }

        .profile-header .info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Main content */
        .main-content {
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
            padding: 30px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .header-nav {
                display: none;
            }
            
            .search-box input {
                width: 200px;
            }
            
            .search-box input:focus {
                width: 240px;
            }
        }

        @media (max-width: 768px) {
            .modern-header {
                padding: 0 15px;
            }
            
            .logo-text {
                display: none;
            }
            
            .search-box {
                display: none;
            }
            
            .profile-info {
                display: none;
            }
            
            .notifications-dropdown {
                width: 300px;
                right: -100px;
            }
        }

        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-content {
            animation: slideDown 0.5s ease;
        }

        /* Badges et indicateurs */
        .badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-admin {
            background: var(--upf-blue);
            color: white;
        }

        .badge-user {
            background: var(--upf-pink);
            color: white;
        }

        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--upf-pink);
        }
    </style>
</head>
<body>

<!-- Header moderne -->
<header class="modern-header">
    <!-- Partie gauche : Logo -->
    <div class="header-left">
        <a href="../index.php" class="logo-wrapper">
            <div class="logo-icon">UPF</div>
            <div class="logo-text">UPF Gestion</div>
        </a>
    </div>

    <!-- Navigation principale (visible seulement si connecté) -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="header-nav">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <!-- Menu Admin -->
            <div class="nav-item">
                <a href="../admin/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="nav-item dropdown">
                <a href="#" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Étudiants</span>
                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="../admin/etudiants/liste.php" class="dropdown-item">
                        <i class="fas fa-list"></i>
                        Liste des étudiants
                    </a>
                    <a href="../admin/etudiants/ajouter.php" class="dropdown-item">
                        <i class="fas fa-plus-circle"></i>
                        Ajouter un étudiant
                    </a>
                    <a href="../admin/etudiants/statistiques.php" class="dropdown-item">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques
                    </a>
                </div>
            </div>
            
            <div class="nav-item dropdown">
                <a href="#" class="nav-link">
                    <i class="fas fa-building"></i>
                    <span>Filières</span>
                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="../admin/filieres/liste.php" class="dropdown-item">
                        <i class="fas fa-list"></i>
                        Liste des filières
                    </a>
                    <a href="../admin/filieres/ajouter.php" class="dropdown-item">
                        <i class="fas fa-plus-circle"></i>
                        Ajouter une filière
                    </a>
                </div>
            </div>
            
            <div class="nav-item">
                <a href="../admin/utilisateurs/liste.php" class="nav-link">
                    <i class="fas fa-user-cog"></i>
                    <span>Utilisateurs</span>
                </a>
            </div>
            
        <?php else: ?>
            <!-- Menu Étudiant -->
            <div class="nav-item">
                <a href="../user/profil.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>Mon Profil</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="../user/notes.php" class="nav-link">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Mes Notes</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="../user/documents.php" class="nav-link">
                    <i class="fas fa-file-pdf"></i>
                    <span>Mes Documents</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="../user/changer_password.php" class="nav-link">
                    <i class="fas fa-key"></i>
                    <span>Changer mot de passe</span>
                </a>
            </div>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <!-- Partie droite -->
    <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Barre de recherche -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Rechercher..." id="globalSearch">
            </div>

            <!-- Notifications -->
            <div class="notifications">
                <button class="notifications-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notifications-badge">3</span>
                </button>
                <div class="notifications-dropdown">
                    <div class="notifications-header">
                        <h3>Notifications</h3>
                        <a href="#">Tout marquer comme lu</a>
                    </div>
                    <div class="notifications-list">
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Nouvel étudiant inscrit</div>
                                <div class="notification-time">Il y a 5 minutes</div>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Document uploadé</div>
                                <div class="notification-time">Il y a 1 heure</div>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Réunion pédagogique</div>
                                <div class="notification-time">Demain à 10h</div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 15px; text-align: center; border-top: 1px solid #e2e8f0;">
                        <a href="#" style="color: var(--upf-pink); text-decoration: none; font-size: 0.9rem;">Voir toutes les notifications</a>
                    </div>
                </div>
            </div>

            <!-- Profil utilisateur -->
            <div class="user-profile">
                <div class="profile-btn">
                    <div class="profile-avatar">
                        <?php 
                        $initiales = '';
                        if (isset($_SESSION['login'])) {
                            $parts = explode('.', $_SESSION['login']);
                            $initiales = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                        }
                        echo $initiales ?: 'U';
                        ?>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name"><?php echo $_SESSION['login']; ?></div>
                        <div class="profile-role">
                            <?php echo $_SESSION['role'] === 'admin' ? 'Administrateur' : 'Étudiant'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="profile-dropdown">
                    <div class="profile-header">
                        <div class="avatar"><?php echo $initiales ?: 'U'; ?></div>
                        <div class="info">
                            <h4><?php echo $_SESSION['login']; ?></h4>
                            <p><?php echo $_SESSION['role'] === 'admin' ? 'Administrateur' : 'Étudiant'; ?></p>
                        </div>
                    </div>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="../admin/dashboard.php" class="dropdown-item">
                            <i class="fas fa-chart-pie"></i>
                            Dashboard
                        </a>
                        <a href="../admin/profil.php" class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            Mon profil
                        </a>
                    <?php else: ?>
                        <a href="../user/profil.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Mon profil
                        </a>
                        <a href="../user/notes.php" class="dropdown-item">
                            <i class="fas fa-graduation-cap"></i>
                            Mes notes
                        </a>
                        <a href="../user/documents.php" class="dropdown-item">
                            <i class="fas fa-file-pdf"></i>
                            Mes documents
                        </a>
                    <?php endif; ?>
                    
                    <div class="dropdown-divider"></div>
                    
                    <a href="../user/changer_password.php" class="dropdown-item">
                        <i class="fas fa-key"></i>
                        Changer mot de passe
                    </a>
                    
                    <a href="../logout.php" class="dropdown-item" style="color: var(--danger);">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Si non connecté, afficher bouton login -->
            <a href="../login.php" class="nav-link" style="background: var(--upf-gradient); color: white; padding: 10px 25px; border-radius: 10px;">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </a>
        <?php endif; ?>
    </div>
</header>

<!-- Main content -->
<main class="main-content">
    <!-- Affichage des messages flash -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?>" style="
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            background: <?php echo $_SESSION['flash']['type'] == 'success' ? '#d1fae5' : '#fee2e2'; ?>;
            color: <?php echo $_SESSION['flash']['type'] == 'success' ? '#065f46' : '#991b1b'; ?>;
            border-left: 4px solid <?php echo $_SESSION['flash']['type'] == 'success' ? '#10b981' : '#ef4444'; ?>;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.3s ease;
        ">
            <span><?php echo $_SESSION['flash']['message']; ?></span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

<script>
// Recherche globale
document.getElementById('globalSearch')?.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        let search = this.value.trim();
        if (search.length > 0) {
            // Rediriger vers la page de recherche appropriée
            <?php if ($_SESSION['role'] === 'admin'): ?>
                window.location.href = '../admin/etudiants/liste.php?search=' + encodeURIComponent(search);
            <?php else: ?>
                window.location.href = '../user/recherche.php?q=' + encodeURIComponent(search);
            <?php endif; ?>
        }
    }
});

// Marquer les notifications comme lues
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', function() {
        this.style.opacity = '0.5';
        // Ici tu peux ajouter AJAX pour marquer comme lu
    });
});

// Animation au scroll
window.addEventListener('scroll', function() {
    let header = document.querySelector('.modern-header');
    if (window.scrollY > 50) {
        header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
        header.style.background = 'rgba(255,255,255,0.98)';
    } else {
        header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.05)';
        header.style.background = 'rgba(255,255,255,0.95)';
    }
});
</script>