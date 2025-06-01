<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) exit;
$user_id = $_SESSION['user_id'];
if (!isset($_POST['post_id'])) exit;
$post_id = intval($_POST['post_id']);

// Vérifier si déjà liké
$stmt = $pdo->prepare("SELECT 1 FROM likes WHERE post_id=? AND utilisateur_id=?");
$stmt->execute([$post_id, $user_id]);
$exists = $stmt->fetchColumn();

if ($exists) {
    // Déjà liké : on enlève le like
    $del = $pdo->prepare("DELETE FROM likes WHERE post_id=? AND utilisateur_id=?");
    $del->execute([$post_id, $user_id]);
} else {
    // Pas encore liké : on ajoute
    $add = $pdo->prepare("INSERT INTO likes (post_id, utilisateur_id) VALUES (?,?)");
    $add->execute([$post_id, $user_id]);
}
header("Location: mur.php"); // ou renvoyer sur la page d'où vient le like
exit;
?>
