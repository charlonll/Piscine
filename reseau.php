<?php
include 'header.php';
require 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];

// Invitations reçues
$stmt = $pdo->prepare("SELECT r.id, u.id as expediteur_id, u.pseudo, u.photo, u.nom, u.prenom 
    FROM relations r 
    JOIN utilisateurs u ON r.id_expediteur = u.id 
    WHERE r.id_destinataire=? AND r.statut='en_attente'");
$stmt->execute([$user_id]);
$invites = $stmt->fetchAll();

// Suggestions (EXCLU les ignorés !)
$sugg = $pdo->prepare("
    SELECT u.id, u.pseudo, u.photo, u.nom, u.prenom
    FROM utilisateurs u
    WHERE u.id != ?
    AND u.id NOT IN (
        SELECT CASE WHEN id_expediteur=? THEN id_destinataire ELSE id_expediteur END
        FROM relations WHERE (id_expediteur=? OR id_destinataire=?) AND statut='accepte'
        UNION
        SELECT id_destinataire FROM relations WHERE id_expediteur=? AND statut='en_attente'
        UNION
        SELECT id_expediteur FROM relations WHERE id_destinataire=? AND statut='en_attente'
        UNION
        SELECT id_ignored FROM ignores WHERE id_user=?
    )
");
$sugg->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$suggestions = $sugg->fetchAll();

// Mon réseau (contacts acceptés)
$contacts = $pdo->prepare("
    SELECT u.id, u.pseudo, u.photo, u.nom, u.prenom
    FROM utilisateurs u
    WHERE u.id != ? AND u.id IN (
        SELECT CASE WHEN id_expediteur=? THEN id_destinataire ELSE id_expediteur END
        FROM relations WHERE (id_expediteur=? OR id_destinataire=?) AND statut='accepte'
    )
");
$contacts->execute([$user_id, $user_id, $user_id, $user_id]);
$mon_reseau = $contacts->fetchAll();

$tab = $_GET['tab'] ?? 'reseau';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mon Réseau</title>
    <style>
        body { font-family:Arial,sans-serif; background:#f6f8fa; margin:0;}
        .reseau-container { max-width:900px; margin:40px auto; background:#fff; border-radius:12px; box-shadow:0 0 10px #d3d9e6; padding:35px;}
        .tabs { display:flex; gap:40px; margin-bottom:18px; }
        .tab-btn {
            padding:10px 28px; background:#eee; border-radius:12px 12px 0 0; border:none; cursor:pointer; font-size:1.13em; color:#24304a;
            margin-right:4px; font-weight:bold;
            text-decoration:none;
            display:inline-block;
        }
        .tab-btn.active { background:#2176ae; color:#fff; }
        .section { margin-bottom:38px;}
        .user-row { display:flex; align-items:center; margin-bottom:14px; padding:8px 0;}
        .reseau-avatar { width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:13px;}
        .reseau-pseudo { font-weight:bold; margin-right:13px;}
        .reseau-meta { color:#888; margin-right:18px;}
        .reseau-action a { margin-right:8px; text-decoration:none; padding:5px 18px; border-radius:8px; background:#2176ae; color:#fff;}
        .reseau-action a.ignorer, .reseau-action a.refuser { background:#888;}
        .titre-section { font-size:1.18em; color:#24304a; font-weight:bold; margin-bottom:13px;}
        a.link-profil { display:flex;align-items:center;text-decoration:none;color:inherit;flex:1;}
    </style>
</head>
<body>
<div class="reseau-container">
    <div class="tabs">
        <a href="?tab=reseau" class="tab-btn <?= $tab=='reseau'?'active':'' ?>">Mon réseau</a>
        <a href="?tab=invites" class="tab-btn <?= $tab=='invites'?'active':'' ?>">Invitations reçues</a>
        <a href="?tab=suggestions" class="tab-btn <?= $tab=='suggestions'?'active':'' ?>">Suggestions</a>
    </div>

    <?php if($tab == 'reseau'): ?>
        <div class="section">
            <div class="titre-section">Mes contacts</div>
            <?php if(count($mon_reseau)==0): ?>
                <div>Aucun contact pour le moment.</div>
            <?php endif; ?>
            <?php foreach($mon_reseau as $c):
                $photo = !empty($c['photo']) ? htmlspecialchars($c['photo']) : 'default_avatar.png';
                $prenom_nom = htmlspecialchars(trim($c['prenom'].' '.$c['nom']));
            ?>
                <div class="user-row">
                    <a class="link-profil" href="profil.php?id=<?= $c['id'] ?>">
                        <img src="<?= $photo ?>" class="reseau-avatar">
                        <span class="reseau-pseudo"><?= $prenom_nom ?> <span class="reseau-meta">(<?= htmlspecialchars($c['pseudo']) ?>)</span></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if($tab == 'invites'): ?>
        <div class="section">
            <div class="titre-section">Invitations reçues</div>
            <?php if(count($invites)==0): ?>
                <div>Aucune invitation pour l’instant.</div>
            <?php endif; ?>
            <?php foreach($invites as $i):
                $photo = !empty($i['photo']) ? htmlspecialchars($i['photo']) : 'default_avatar.png';
                $prenom_nom = htmlspecialchars(trim($i['prenom'].' '.$i['nom']));
            ?>
                <div class="user-row">
                    <a class="link-profil" href="profil.php?id=<?= $i['expediteur_id'] ?>">
                        <img src="<?= $photo ?>" class="reseau-avatar">
                        <span class="reseau-pseudo"><?= $prenom_nom ?> <span class="reseau-meta">(<?= htmlspecialchars($i['pseudo']) ?>)</span></span>
                    </a>
                    <span class="reseau-action">
                        <a href="action_reseau.php?action=accepter&id=<?= $i['id'] ?>">Accepter</a>
                        <a href="action_reseau.php?action=refuser&id=<?= $i['id'] ?>" class="refuser">Refuser</a>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if($tab == 'suggestions'): ?>
        <div class="section">
            <div class="titre-section">Suggestions de contacts</div>
            <?php if(count($suggestions)==0): ?>
                <div>Aucune suggestion pour le moment.</div>
            <?php endif; ?>
            <?php foreach($suggestions as $u):
                $photo = !empty($u['photo']) ? htmlspecialchars($u['photo']) : 'default_avatar.png';
                $prenom_nom = htmlspecialchars(trim($u['prenom'].' '.$u['nom']));
            ?>
                <div class="user-row">
                    <a class="link-profil" href="profil.php?id=<?= $u['id'] ?>">
                        <img src="<?= $photo ?>" class="reseau-avatar">
                        <span class="reseau-pseudo"><?= $prenom_nom ?> <span class="reseau-meta">(<?= htmlspecialchars($u['pseudo']) ?>)</span></span>
                    </a>
                    <span class="reseau-action">
                        <a href="action_reseau.php?action=ajouter&id=<?= $u['id'] ?>">Ajouter</a>
                        <a href="action_reseau.php?action=ignorer&id=<?= $u['id'] ?>" class="ignorer">Ignorer</a>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
