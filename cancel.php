<?php
require_once 'includes/config.php';

// Récupération de l'ID de transaction
$transaction_id = $_GET['transaction_id'] ?? '';

// Mise à jour du statut du paiement
if ($transaction_id) {
    $stmt = $pdo->prepare("UPDATE paiement SET status = 'echec' WHERE transaction_id = ?");
    $stmt->execute([$transaction_id]);
}

$_SESSION['error'] = "Le paiement a été annulé.";
header('Location: candidates.php');
exit();
