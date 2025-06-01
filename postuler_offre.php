<?php
// 1. Activer les erreurs pour déboguer
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Connexion et sécurité
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$offre_id = $_GET['offre_id'] ?? $_POST['offre_id'] ?? null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv']) && $offre_id) {
    $cv = $_FILES['cv'];
    $texte_motivation = trim($_POST['message'] ?? '');
    $allowed = ['pdf', 'doc', 'docx'];
    $ext = strtolower(pathinfo($cv['name'], PATHINFO_EXTENSION));

    if ($cv['error'] === 0 && in_array($ext, $allowed)) {
        // 3. Vérifier que le dossier cv/ existe
        $dir = 'cv/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'cv_user' . $user_id . '_offre' . $offre_id . '_' . time() . '.' . $ext;
        $path = $dir . $filename;

        if (move_uploaded_file($cv['tmp_name'], $path)) {
            // 4. Enregistrer en base
            $stmt = $pdo->prepare("INSERT INTO candidatures (utilisateur_id, offre_id, chemin_cv, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $offre_id, $path, $texte_motivation]);

            $message = "✅ Votre candidature a été envoyée avec succès.";
            header("refresh:3;url=mur.php"); // redirection après 3 sec
        } else {
            $message = "❌ Échec du téléchargement du fichier.";
        }
    } else {
        $message = "❌ Format de fichier invalide. Seuls les fichiers PDF, DOC ou DOCX sont autorisés.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Postuler à une offre</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; }
        .form-container {
            max-width: 550px; margin: 60px auto; background: #fff;
            padding: 30px; border-radius: 12px;
            box-shadow: 0 0 12px #ccc; text-align: center;
        }
        input[type=file], textarea {
            width: 90%; margin: 15px auto; padding: 8px;
            border-radius: 6px; border: 1px solid #ccc;
        }
        textarea { height: 100px; resize: vertical; }
        input[type=submit] {
            background: #2176ae; color: white; padding: 10px 22px;
            border: none; border-radius: 8px; cursor: pointer;
        }
        .msg {
            margin-top: 20px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Déposer votre candidature</h2>

    <?php if ($message): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
        <p style="margin-top:15px;">
            Redirection en cours...<br>
            <a href="mur.php">Cliquez ici si la redirection ne fonctionne pas</a>
        </p>
    <?php else: ?>
        <?php if ($offre_id): ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="offre_id" value="<?= htmlspecialchars($offre_id) ?>">

                <label for="cv">Sélectionnez votre CV :</label><br>
                <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx" required><br><br>

                <label for="message">Message de motivation (optionnel) :</label><br>
                <textarea name="message" id="message" placeholder="Pourquoi postulez-vous à cette offre ?"></textarea><br>

                <input type="submit" value="Envoyer ma candidature">
            </form>
        <?php else: ?>
            <p>❌ Aucune offre sélectionnée.</p>
            <a href="offres.php">Retour aux offres</a>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>