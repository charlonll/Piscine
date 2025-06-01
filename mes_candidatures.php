<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT c.*, o.titre, o.entreprise
    FROM candidatures c
    JOIN offres o ON c.offre_id = o.id
    WHERE c.utilisateur_id = ?
    ORDER BY c.date_postulation DESC
");
$stmt->execute([$user_id]);
$candidatures = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes candidatures</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
        }
        .container {
            max-width: 750px;
            margin: 50px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .candidature {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }
        .titre-offre {
            font-size: 1.2em;
            font-weight: bold;
        }
        .entreprise {
            color: #555;
        }
        .meta {
            font-size: 0.95em;
            color: #888;
            margin-top: 8px;
        }
        .cv-link {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 12px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }
        .cv-link:hover {
            background: #2176ae;
        }
        .motivation {
            margin-top: 12px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Mes candidatures</h2>

    <?php if (empty($candidatures)): ?>
        <p style="text-align:center;">Vous n'avez encore postulé à aucune offre.</p>
    <?php else: ?>
        <?php foreach ($candidatures as $c): ?>
            <div class="candidature">
                <div class="titre-offre"><?= htmlspecialchars($c['titre']) ?></div>
                <div class="entreprise"><?= htmlspecialchars($c['entreprise']) ?></div>
                <div class="meta">Postulé le <?= date('d/m/Y H:i', strtotime($c['date_postulation'])) ?></div>

                <a class="cv-link" href="<?= htmlspecialchars($c['chemin_cv']) ?>" target="_blank">📄 Voir le CV envoyé</a>

                <?php if (!empty($c['message'])): ?>
                    <div class="motivation">
                        <b>Message de motivation :</b><br>
                        <?= nl2br(htmlspecialchars($c['message'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
