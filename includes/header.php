<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/Gestion_UPF/');
}

$title = $title ?? "Administration";

/* sécurisation */
$user_login = htmlspecialchars($_SESSION['login'] ?? '');
$user_role  = $_SESSION['role'] ?? '';
$user_id    = $_SESSION['user_id'] ?? null;

/* Initiales avatar */
$initiales = 'U';
if ($user_login) {
    $parts = explode('.', $user_login);
    $initiales =
        strtoupper(substr($parts[0] ?? '',0,1) .
        substr($parts[1] ?? '',0,1));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>UPF Gestion - <?= htmlspecialchars($title) ?></title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= BASE_URL ?>assets/style.css">

<style>

:root{
--upf-blue:#294898;
--upf-pink:#C72C82;
--gradient:linear-gradient(135deg,var(--upf-blue),var(--upf-pink));
--dark:#1e293b;
--gray:#64748b;
--light:#f8fafc;
--danger:#ef4444;
--header-height:70px;
}

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:'Inter',sans-serif;
background:#f1f5f9;
color:var(--dark);
}

.modern-header{
height:var(--header-height);
display:flex;
align-items:center;
justify-content:space-between;
padding:0 30px;
background:white;
border-bottom:1px solid #eee;
position:fixed;
top:0;
left:0;
right:0;
z-index:999;
box-shadow:0 2px 10px rgba(0,0,0,.05);
}

.logo-wrapper{
display:flex;
align-items:center;
gap:12px;
text-decoration:none;
}

.logo-icon{
width:42px;
height:42px;
border-radius:10px;
display:flex;
align-items:center;
justify-content:center;
font-weight:700;
color:white;
background:var(--gradient);
}

.logo-text{
font-weight:700;
font-size:20px;
background:var(--gradient);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

.header-nav{
display:flex;
gap:10px;
}

.nav-link{
padding:8px 14px;
border-radius:8px;
text-decoration:none;
font-weight:500;
color:var(--gray);
display:flex;
gap:6px;
align-items:center;
transition:.2s;
}

.nav-link:hover{
background:#f1f5f9;
color:var(--upf-blue);
}

.nav-link.active{
background:var(--gradient);
color:white;
}

.header-right{
display:flex;
align-items:center;
gap:15px;
}

.search-box{
position:relative;
}

.search-box input{
height:40px;
padding:0 14px 0 35px;
border:1px solid #ddd;
border-radius:8px;
width:230px;
}

.search-box i{
position:absolute;
left:10px;
top:50%;
transform:translateY(-50%);
color:var(--gray);
}

.profile-btn{
display:flex;
align-items:center;
gap:10px;
border:none;
background:#f1f5f9;
padding:5px 10px;
border-radius:10px;
cursor:pointer;
}

.profile-avatar{
width:36px;
height:36px;
background:var(--gradient);
border-radius:8px;
display:flex;
align-items:center;
justify-content:center;
color:white;
font-weight:600;
}

.profile-dropdown{
position:absolute;
right:0;
top:60px;
background:white;
border-radius:10px;
box-shadow:0 10px 25px rgba(0,0,0,.1);
width:220px;
display:none;
}

.user-profile:hover .profile-dropdown{
display:block;
}

.dropdown-item{
display:flex;
gap:10px;
padding:12px 16px;
text-decoration:none;
color:var(--dark);
font-size:14px;
}

.dropdown-item:hover{
background:#f8fafc;
color:var(--upf-pink);
}

.dropdown-divider{
height:1px;
background:#eee;
margin:6px 0;
}

.main-content{
margin-top:var(--header-height);
padding:30px;
}

</style>

</head>

<body>

<header class="modern-header">

<a href="<?= BASE_URL ?>" class="logo-wrapper">
<div class="logo-icon">UPF</div>
<div class="logo-text">UPF Gestion</div>
</a>

<?php if($user_id): ?>

<nav class="header-nav">

<?php if($user_role==="admin"): ?>

<a href="<?= BASE_URL ?>./admin/dashboard.php" class="nav-link">
<i class="fas fa-chart-pie"></i> Dashboard
</a>

<a href="<?= BASE_URL ?>admin/etudiants/liste.php" class="nav-link">
<i class="fas fa-users"></i> Étudiants
</a>

<a href="<?= BASE_URL ?>admin/filieres/liste.php" class="nav-link">
<i class="fas fa-building"></i> Filières
</a>

<a href="<?= BASE_URL ?>admin/utilisateurs/liste.php" class="nav-link">
<i class="fas fa-user-cog"></i> Utilisateurs
</a>

<?php else: ?>

<a href="<?= BASE_URL ?>user/profil.php" class="nav-link">
<i class="fas fa-user"></i> Profil
</a>

<a href="<?= BASE_URL ?>user/notes.php" class="nav-link">
<i class="fas fa-graduation-cap"></i> Notes
</a>

<a href="<?= BASE_URL ?>user/documents.php" class="nav-link">
<i class="fas fa-file"></i> Documents
</a>

<?php endif; ?>

</nav>

<div class="header-right">

<div class="search-box">
<i class="fas fa-search"></i>
<input type="text" id="globalSearch" placeholder="Rechercher...">
</div>

<div class="user-profile">

<button class="profile-btn">
<div class="profile-avatar"><?= $initiales ?></div>

<div>
<div style="font-weight:600;font-size:14px">
<?= $user_login ?>
</div>

<div style="font-size:11px;color:gray">
<?= $user_role==="admin" ? "Administrateur" : "Étudiant" ?>
</div>
</div>

</button>

<div class="profile-dropdown">

<a href="<?= BASE_URL ?>admin/profil.php" class="dropdown-item">
<i class="fas fa-user"></i> Mon Profil
</a>

<a href="<?= BASE_URL ?>user/changer_password.php" class="dropdown-item">
<i class="fas fa-key"></i> Changer mot de passe
</a>

<div class="dropdown-divider"></div>

<a href="<?= BASE_URL ?>./auth/logout.php" class="dropdown-item" style="color:red">
<i class="fas fa-sign-out-alt"></i> Déconnexion
</a>

</div>

</div>

</div>

<?php else: ?>

<a href="<?= BASE_URL ?>login.php" class="nav-link">
<i class="fas fa-sign-in-alt"></i> Connexion
</a>

<?php endif; ?>

</header>

<main class="main-content">

<script>

document.getElementById("globalSearch")?.addEventListener("keyup",function(e){

if(e.key==="Enter"){

let q=this.value.trim()

if(q.length>0){

window.location.href="<?= BASE_URL ?>admin/etudiants/liste.php?search="+encodeURIComponent(q)

}

}

})

</script>