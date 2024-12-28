<?php
require_once 'includes/config.php';
require_once 'includes/cinetpay-config.php';

// Récupération des données de la notification
$data = json_decode(file_get_contents('php://input'), true);

if ($data['status'] == 'ACCEPTED') {
    // Mise à jour du statut du paiement
    $stmt = $pdo->prepare("UPDATE paiement SET status = 'termine' WHERE transaction_id = ?");
    $stmt->execute([$data['transaction_id']]);
    
    // Mise à jour du nombre de votes du candidat
    $stmt = $pdo->prepare("
        UPDATE candidats 
        SET votes = votes + 1 
        WHERE id = (
            SELECT id_candidat 
            FROM paiement 
            WHERE transaction_id = ?
        )
    ");
    $stmt->execute([$data['transaction_id']]);
}
