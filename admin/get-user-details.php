<?php
require_once '../includes/config.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID utilisateur manquant']);
    exit();
}

$userId = (int)$_GET['id'];

// Récupération des informations détaillées de l'utilisateur
$stmt = $pdo->prepare("
    SELECT u.*,
           COUNT(DISTINCT v.id) as vote_count,
           COALESCE(SUM(p.amount), 0) as total_spent,
           MAX(v.created_at) as last_vote_date
    FROM users u
    LEFT JOIN votes v ON u.id = v.voter_id
    LEFT JOIN payments p ON v.payment_id = p.id AND p.status = 'Completed'
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des dernières activités (votes et paiements)
$stmt = $pdo->prepare("
    SELECT 
        'Vote' as type,
        v.created_at as date,
        c.name as details,
        p.amount,
        p.status
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.id
    JOIN payments p ON v.payment_id = p.id
    WHERE v.voter_id = ?
    ORDER BY v.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatage des données pour l'affichage
$response = [
    'username' => $user['username'],
    'email' => $user['email'],
    'created_at' => date('d/m/Y H:i', strtotime($user['created_at'])),
    'vote_count' => $user['vote_count'],
    'total_spent' => number_format($user['total_spent'], 2),
    'last_vote_date' => $user['last_vote_date'] ? date('d/m/Y H:i', strtotime($user['last_vote_date'])) : null,
    'activities' => array_map(function($activity) {
        return [
            'date' => date('d/m/Y H:i', strtotime($activity['date'])),
            'action' => $activity['type'],
            'details' => "Vote pour {$activity['details']} - {$activity['amount']} FCFA ({$activity['status']})"
        ];
    }, $activities)
];

header('Content-Type: application/json');
echo json_encode($response);
