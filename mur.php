<?php
include 'header.php';
require 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];

// 1. Récupère mes contacts (réseau)
$contacts = $pdo->prepare("
    SELECT CASE WHEN id_expediteur=? THEN id_destinataire ELSE id_expediteur END AS contact_id
    FROM relations WHERE (id_expediteur=? OR id_destinataire=?) AND statut='accepte'
");
$contacts->execute([$user_id, $user_id, $user_id]);
$ids = [$user_id];
foreach ($contacts as $c) $ids[] = $c['contact_id'];
$ids_unique = array_unique($ids);
$placeholders = implode(',', array_fill(0, count($ids_unique), '?'));

// 2. Affiche les posts selon visibilité
$stmt = $pdo->prepare("
    SELECT p.*, u.pseudo, u.photo, u.nom, u.prenom
    FROM posts p
    JOIN utilisateurs u ON p.utilisateur_id = u.id
    WHERE
        p.visibilite = 'public'
        OR (p.visibilite = 'reseau' AND p.utilisateur_id IN ($placeholders))
        OR (p.visibilite = 'selection' AND (JSON_CONTAINS(p.selection_amis, '\"$user_id\"')))
        OR (p.utilisateur_id = ?)
    ORDER BY p.date_post DESC
");
$stmt->execute(array_merge($ids_unique, [$user_id]));
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fil d’actualité</title>
    <style>
        body { font-family:Arial, sans-serif; background:#f6f8fa; margin:0; }
        .feed-container { max-width:750px; margin:40px auto; }
        .post { background:#fff; border-radius:10px; box-shadow:0 0 8px #d7dbe4; padding:18px; margin-bottom:18px;}
        .post img, .post video { max-width:100%; border-radius:10px;}
        .post .meta { font-size:0.97em; color:#888;}
        .like-btn { border:none; background:#3498db; color:#fff; border-radius:6px; padding:5px 12px; cursor:pointer; margin-right:6px;}
        .like-btn:hover { background:#2176ae; }
        .comment-section {margin:10px 0 5px 0;}
        .comment-section b {color:#2176ae;}
        .comment-form input[type="text"] {width:70%;padding:4px 8px;}
        .comment-form button {border:none;background:#5eb95e;color:#fff;border-radius:5px;padding:4px 11px;cursor:pointer;}
        .supprimer-btn {background:#e74c3c;color:#fff;border:none;padding:5px 11px;border-radius:6px; margin-left:7px;}
        .supprimer-btn:hover {background:#c0392b;}
        .badge-visi {font-size:0.92em; color:#888;}
    </style>
</head>
<body>

<div class="feed-container">
    <h2>Fil d’actualité</h2>
    <?php foreach($posts as $p): ?>
        <div class="post">
            <div class="meta">
                <a href="profil.php?id=<?= $p['utilisateur_id'] ?>">
                    <img src="<?= htmlspecialchars($p['photo'] ?? 'default_avatar.png') ?>" style="width:30px;height:30px;border-radius:50%;vertical-align:middle;">
                    <b><?= htmlspecialchars(trim(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? ''))) ?> (<?= htmlspecialchars($p['pseudo']) ?>)</b>
                </a>
                — Posté le <?= htmlspecialchars($p['date_post']) ?>
                <span class="badge-visi">
                <?php
                    if($p['visibilite']=='public')      echo "🌍 Public";
                    elseif($p['visibilite']=='reseau')  echo "👥 Mon réseau";
                    elseif($p['visibilite']=='selection') echo "🔒 Sélection";
                ?>
                </span>
            </div>
            <div>
            <?php if($p['type']=='photo' && $p['fichier']): ?>
                <img src="<?= htmlspecialchars($p['fichier']) ?>" alt="photo">
            <?php elseif($p['type']=='video' && $p['fichier']): ?>
                <video src="<?= htmlspecialchars($p['fichier']) ?>" controls></video>
            <?php endif; ?>
            </div>
            <div style="margin-top:8px;"><?= nl2br(htmlspecialchars($p['description'])) ?></div>
            <?php if($p['lieu']): ?><div class="meta">Lieu : <?= htmlspecialchars($p['lieu']) ?></div><?php endif; ?>
            <?php if($p['date_event']): ?><div class="meta">Date évènement : <?= htmlspecialchars($p['date_event']) ?></div><?php endif; ?>
            <?php if($p['humeur']): ?><div class="meta">Humeur : <?= htmlspecialchars($p['humeur']) ?></div><?php endif; ?>

            <!-- LIKE / COMMENTAIRES -->
            <?php
            // Likes
            $likeStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id=?");
            $likeStmt->execute([$p['id']]);
            $nbLikes = $likeStmt->fetchColumn();

            $likedByMe = $pdo->prepare("SELECT 1 FROM likes WHERE post_id=? AND utilisateur_id=?");
            $likedByMe->execute([$p['id'], $user_id]);
            $isLiked = $likedByMe->fetchColumn();
            ?>
            <form method="post" action="like_post.php" style="display:inline;">
                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                <button type="submit" class="like-btn"><?= $isLiked ? "Je n'aime plus" : "J'aime" ?> (<?= $nbLikes ?>)</button>
            </form>

            <!-- Commentaires -->
            <div class="comment-section">
            <?php
            $comStmt = $pdo->prepare("SELECT c.*, u.pseudo FROM commentaires c JOIN utilisateurs u ON c.utilisateur_id=u.id WHERE c.post_id=? ORDER BY c.date_commentaire");
            $comStmt->execute([$p['id']]);
            foreach($comStmt as $c): ?>
                <div>
                    <b><?= htmlspecialchars($c['pseudo']) ?></b>: <?= htmlspecialchars($c['texte']) ?>
                </div>
            <?php endforeach; ?>
            </div>
            <form method="post" action="commenter_post.php" class="comment-form">
                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                <input type="text" name="texte" placeholder="Ajouter un commentaire..." required>
                <button type="submit">Commenter</button>
            </form>

            <!-- BOUTON SUPPRIMER POUR L'AUTEUR UNIQUEMENT -->
            <?php if ($p['utilisateur_id'] == $user_id): ?>
                <form method="post" action="supprimer_post.php" style="display:inline;">
                    <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                    <button type="submit" onclick="return confirm('Supprimer ce post ?')" class="supprimer-btn">Supprimer</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
