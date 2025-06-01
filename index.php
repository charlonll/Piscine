<?php
session_start();
$type = $_SESSION['type'] ?? null;
$pseudo = $_SESSION['pseudo'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accueil - Gestion des Utilisateurs</title>
    <style>
        body {
            background: #f5f5f5;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .logo-container {
            text-align: center;
            margin-top: 40px;
        }

        .logo-container img {
            height: 80px;
        }

        .container {
            max-width: 400px;
            margin: 30px auto 80px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 0 24px #bbb;
            padding: 35px;
            text-align: center;
        }

        h1 {
            font-size: 2em;
            margin-bottom: 28px;
        }

        a.btn {
            display: block;
            margin: 16px 0;
            background: #337ab7;
            color: #fff;
            padding: 12px 0;
            border-radius: 10px;
            text-decoration: none;
            font-size: 1.13em;
            transition: background 0.2s;
        }

        a.btn:hover {
            background: #23527c;
        }

        .info {
            margin: 18px 0 8px 0;
            color: #666;
        }

        .role {
            margin: 10px 0 25px 0;
            font-weight: bold;
            color: #3498db;
        }

        .admin {
            color: #e67e22;
        }
    </style>
</head>
<body>

<!-- Logo du site -->
<div class="logo-container">
    <a href="index.php">
        <img src="profils/logo.png" alt="Logo ECEin">
    </a>
</div>

<!-- Contenu principal -->
<div class="container">
    <h1>Bienvenue sur ECEIN</h1>

    <?php if (!$type): ?>
        <a href="login.php" class="btn">Connexion / Inscription</a>
    <?php else: ?>
        <div class="role">
            Connecté en tant que <span class="<?= $type == 'admin' ? 'admin' : '' ?>">
                <?= htmlspecialchars($type) ?>
            </span>
        </div>
        <a href="mur.php" class="btn">Mon mur personnel</a>
        <a href="update_profil.php" class="btn">Paramètres du profil</a>
        <?php if ($type == 'admin'): ?>
            <a href="admin.php" class="btn">Administration (Gestion utilisateurs)</a>
        <?php endif; ?>
        <a href="logout.php" class="btn" style="background:#e74c3c;">Se déconnecter</a>
    <?php endif; ?>

    <div class="info">
        <?php
        if ($type) {
            echo "Bienvenue, <b>" . htmlspecialchars($pseudo) . "</b>";
        } else {
            echo "Vous n'êtes pas connecté.";
        }
        ?>
    </div>
</div>

</body>
</html>
