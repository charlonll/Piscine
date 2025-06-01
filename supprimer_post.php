<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$type = $_SESSION['type'] ?? 'auteur';

// Redirection personnalisée après suppression
$redirect = 'mur.php';
if (isset($_POST['redirect']) && strpos($_POST['redirect'], 'profil.php') === 0) {
    $redirect = $_POST['redirect'];
}

if (isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);

    $stmt = $pdo->prepare("SELECT utilisateur_id, fichier FROM posts WHERE id=?");
    $stmt->execute([$post_id]);
    $data = $stmt->fetch();

    // Autorisé : admin OU auteur
    if ($data && ($data['utilisateur_id'] == $user_id || $type == 'admin')) {
        if (!empty($data['fichier']) && file_exists($data['fichier'])) {
            unlink($data['fichier']);
        }
        $pdo->prepare("DELETE FROM posts WHERE id=?")->execute([$post_id]);
        $pdo->prepare("DELETE FROM likes WHERE post_id=?")->execute([$post_id]);
        $pdo->prepare("DELETE FROM commentaires WHERE post_id=?")->execute([$post_id]);
    }
}
header('Location: ' . $redirect);
exit;
?>
