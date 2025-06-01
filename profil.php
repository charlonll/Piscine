<?php
include 'header.php'; // Affiche la barre du haut partout
if (!isset($_GET['id'])) { echo "Utilisateur inconnu."; exit; }
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { echo "Utilisateur inconnu."; exit; }

$pseudo = htmlspecialchars($user['pseudo']);
$nom = htmlspecialchars($user['nom'] ?? '');
$prenom = htmlspecialchars($user['prenom'] ?? '');
$email = htmlspecialchars($user['email']);
$photo = !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'default_avatar.png';
$image_fond = !empty($user['image_fond']) ? htmlspecialchars($user['image_fond']) : 'default_banner.jpg';
?>
<style>
body { background:#f5f8fc; }
.banner { width:100%; height:220px; background:#aaa; background-position:center; background-size:cover; }
.profile-card {
    width:95%; max-width:720px; margin:auto; margin-top:-75px; background:#fff;
    border-radius:14px; box-shadow:0 2px 16px #bac3db;
    padding:24px; display:flex; align-items:center; gap:36px;
    position:relative;
}
.avatar { width:130px; height:130px; border-radius:50%; border:6px solid #fff; object-fit:cover; }
.profile-info { flex:1; }
.profile-info h2 { margin:0 0 6px 0; font-size:2em;}
.profile-info .meta { color:#5a6572; font-size:1.13em;}
.profile-actions { text-align:right; }
.profile-actions a, .profile-actions button {
    display:inline-block; margin-top:10px; margin-right:7px; padding:7px 16px; border:none; border-radius:7px;
    background:#2176ae; color:#fff; font-size:1em; text-decoration:none; cursor:pointer;
}
.profile-actions a:hover, .profile-actions button:hover { background:#174d72;}
.feed-container { max-width:750px; margin:40px auto; }
.post { background:#fff; border-radius:10px; box-shadow:0 0 8px #d7dbe4; padding:18px; margin-bottom:18px;}
.post img, .post video { max-width:100%; border-radius:10px;}
.post .meta { font-size:0.97em; color:#888;}
</style>
<div class="banner" style="background-image:url('<?= $image_fond ?>');"></div>
<div class="profile-card">
    <img src="<?= $photo ?>" alt="Avatar" class="avatar">
    <div class="profile-info">
        <h2><?= $prenom . ' ' . $nom ?> <span style="font-size:0.8em; color:#3498db;"><?= $pseudo ?></span></h2>
        <div class="meta"><?= $email ?></div>
        <?php if ($user['id'] == $_SESSION['user_id']): ?>
            <div class="profile-actions">
                <a href="update_profil.php">Modifier mon profil</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Les posts de l'utilisateur -->
<div class="feed-container">
    <h2>Ses publications</h2>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE utilisateur_id=? ORDER BY date_post DESC");
    $stmt->execute([$id]);
    foreach($stmt as $p): ?>
        <div class="post">
            <div class="meta">Posté le <?= htmlspecialchars($p['date_post']) ?></div>
            <?php if($p['type']=='photo' && $p['fichier']): ?>
                <img src="<?= htmlspecialchars($p['fichier']) ?>" alt="photo">
            <?php elseif($p['type']=='video' && $p['fichier']): ?>
                <video src="<?= htmlspecialchars($p['fichier']) ?>" controls></video>
            <?php endif; ?>
            <div style="margin-top:8px;"><?= nl2br(htmlspecialchars($p['description'])) ?></div>
            <?php if($p['lieu']): ?><div class="meta">Lieu : <?= htmlspecialchars($p['lieu']) ?></div><?php endif; ?>
            <?php if($p['date_event']): ?><div class="meta">Date évènement : <?= htmlspecialchars($p['date_event']) ?></div><?php endif; ?>
            <?php if($p['humeur']): ?><div class="meta">Humeur : <?= htmlspecialchars($p['humeur']) ?></div><?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
