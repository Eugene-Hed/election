<?php
require_once '../includes/config.php';
require_once 'includes/auth.php';

checkAdmin();

// Statistiques globales
$stats = [
    'total_votes' => $pdo->query("SELECT COUNT(*) FROM paiement WHERE status = 'termine'")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(montant) FROM paiement WHERE status = 'termine'")->fetchColumn(),
    'miss_candidates' => $pdo->query("SELECT COUNT(*) FROM candidats WHERE categorie = 'Miss'")->fetchColumn(),
    'master_candidates' => $pdo->query("SELECT COUNT(*) FROM candidats WHERE categorie = 'Master'")->fetchColumn(),
    'pending_payments' => $pdo->query("SELECT COUNT(*) FROM paiement WHERE status = 'en attente'")->fetchColumn()
];

// Derniers paiements
$recent_payments = $pdo->query("
    SELECT p.*, c.nom as nom_candidat, c.categorie 
    FROM paiement p 
    JOIN candidats c ON p.id_candidat = c.id 
    ORDER BY p.dates DESC 
    LIMIT 8
")->fetchAll();

// Top candidats
$top_candidates = $pdo->query("
    SELECT c.*, 
           COUNT(p.id) as vote_count,
           (COUNT(p.id) * 100.0 / (SELECT COUNT(*) FROM paiement WHERE status = 'termine')) as vote_percentage
    FROM candidats c
    LEFT JOIN paiement p ON c.id = p.id_candidat AND p.status = 'termine'
    GROUP BY c.id
    ORDER BY vote_count DESC
    LIMIT 5
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gold: #DAA520;
            --secondary-gold: #FFD700;
            --dark-gold: #B8860B;
        }

        .stats-card {
            background: linear-gradient(45deg, var(--primary-gold), var(--secondary-gold));
            color: white;
            border: none;
            transition: transform 0.3s;
            box-shadow: 0 4px 15px rgba(218, 165, 32, 0.2);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: var(--primary-gold);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .progress {
            height: 8px;
            background-color: #f0f0f0;
        }

        .progress-bar {
            background-color: var(--primary-gold);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Tableau de bord</h2>
                <div class="date text-muted">
                    <?php echo date('d/m/Y H:i'); ?>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="card-title mb-0"><?php echo number_format($stats['total_votes']); ?></h3>
                                    <p class="card-text">Votes totaux</p>
                                </div>
                                <i class="fas fa-vote-yea fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="card-title mb-0"><?php echo number_format($stats['total_revenue']); ?> FCFA</h3>
                                    <p class="card-text">Revenus totaux</p>
                                </div>
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="card-title mb-0"><?php echo $stats['miss_candidates']; ?></h3>
                                    <p class="card-text">Candidates Miss</p>
                                </div>
                                <i class="fas fa-female fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="card-title mb-0"><?php echo $stats['master_candidates']; ?></h3>
                                    <p class="card-text">Candidats Master</p>
                                </div>
                                <i class="fas fa-male fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Derniers paiements -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Derniers paiements</h5>
                            <a href="payments.php" class="btn btn-sm btn-light">Voir tout</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Numéro</th>
                                            <th>Candidat</th>
                                            <th>Montant</th>
                                            <th>Votes</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_payments as $payment): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($payment['dates'])); ?></td>
                                                <td><?php echo escape($payment['numvotant']); ?></td>
                                                <td><?php echo escape($payment['nom_candidat']); ?></td>
                                                <td><?php echo number_format($payment['montant']); ?> FCFA</td>
                                                <td><?php echo $payment['nbvote']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $payment['status'] === 'termine' ? 'success' : 
                                                        ($payment['status'] === 'en attente' ? 'warning' : 'danger'); ?>">
                                                        <?php echo escape($payment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top candidats -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Top 5 des candidats</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach($top_candidates as $candidate): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span><?php echo escape($candidate['nom']); ?></span>
                                        <span class="badge bg-light text-dark">
                                            <?php echo $candidate['votes']; ?> votes
                                        </span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo round($candidate['vote_percentage']); ?>%">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
