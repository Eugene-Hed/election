<?php
require_once 'includes/config.php';

$transaction_id = $_GET['transaction_id'] ?? '';
$stmt = $pdo->prepare("SELECT status FROM paiement WHERE transaction_id = ?");
$stmt->execute([$transaction_id]);
$status = $stmt->fetchColumn();

if ($status === 'termine') {
    $_SESSION['success'] = "Votre vote a été enregistré avec succès !";
} else {
    $_SESSION['error'] = "Une erreur est survenue lors du paiement.";
}

header('Location: candidates.php');
exit();
