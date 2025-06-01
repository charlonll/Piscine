<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) exit;
$user_id = $_SESSION['user_id'];
if (!isset($_POST['post_id']) || !isset($_POST['texte'])) exit;
$post_id = intval($_POST['post_id']);
$texte = trim($_POST['texte']);
if ($texte == '') exit;

// Insertion du commentaire
$stmt = $pdo->prepare("INSERT INTO commentaires (post_id, utilisateur_id, texte) VALUES (?,?,?)");
$stmt->execute([$post_id, $user_id, $texte]);
header("Location: mur.php");
exit;
?>
