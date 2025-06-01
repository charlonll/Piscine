<?php
session_start();
require 'config.php';

// Inscription
if (isset($_POST['register'])) {
    $pseudo = trim($_POST['reg_pseudo']);
    $email = trim($_POST['reg_email']);
    $nom = trim($_POST['reg_nom']);
    $password = $_POST['reg_password'];
    $type = 'auteur'; // Par défaut

    // Vérification si déjà existant
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE pseudo=? OR email=?");
    $check->execute([$pseudo, $email]);
    if ($check->fetch()) {
        $reg_message = "Pseudo ou email déjà utilisé.";
    } else {
        // Mot de passe stocké en CLAIR
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe, nom, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$pseudo, $email, $password, $nom, $type]);
        $reg_message = "Inscription réussie ! Connecte-toi.";
    }
}

// Connexion
if (isset($_POST['login'])) {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo=? AND email=?");
    $stmt->execute([$pseudo, $email]);
    $user = $stmt->fetch();
    // Vérification en clair
    if ($user && $password == $user['mot_de_passe']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['type'] = $user['type'];
        setcookie("pseudo", $pseudo, time() + (86400 * 30), "/");
        setcookie("email", $email, time() + (86400 * 30), "/");
        header('Location: mur.php');
        exit;
    } else {
        $message = "Identifiants incorrects.";
    }
}

// Remplissage auto si cookies
$pseudo_cookie = $_COOKIE['pseudo'] ?? '';
$email_cookie = $_COOKIE['email'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion / Inscription</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eee; }
        .container { background: #fff; padding: 30px; border-radius: 15px; width: 320px; margin: 80px auto; box-shadow: 0 0 12px #bbb; }
        h2 { text-align: center; }
        form { margin-bottom: 25px; }
        input[type=text], input[type=password], input[type=email] { width: 95%; padding: 8px; margin: 8px 0; }
        input[type=submit] { padding: 8px 24px; border-radius: 10px; border: none; background: #337ab7; color: #fff; cursor: pointer; }
        .msg { color: red; text-align: center; }
        .success { color: green; text-align: center; }
    </style>
</head>
<body>
<div class="container">

    <h2>Connexion</h2>
    <?php if (isset($message)) echo "<div class='msg'>$message</div>"; ?>
    <form method="post">
        <input type="text" name="pseudo" placeholder="Pseudo" required value="<?= htmlspecialchars($pseudo_cookie) ?>">
        <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($email_cookie) ?>">
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="submit" name="login" value="Se connecter">
    </form>

    <h2>Inscription</h2>
    <?php if (isset($reg_message)) echo "<div class='".(strpos($reg_message,'réussie')?'success':'msg')."'>$reg_message</div>"; ?>
    <form method="post">
        <input type="text" name="reg_pseudo" placeholder="Pseudo" required>
        <input type="email" name="reg_email" placeholder="Email" required>
        <input type="text" name="reg_nom" placeholder="Nom (optionnel)">
        <input type="password" name="reg_password" placeholder="Mot de passe" required>
        <input type="submit" name="register" value="S'inscrire">
    </form>

</div>
</body>
</html>
