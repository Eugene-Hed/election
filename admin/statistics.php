<?php
require_once '../includes/config.php';
require_once 'includes/auth.php';

checkAdmin();

// Statistiques globales
$global_stats = [
    'total_votes' => $pdo->query("SELECT COUNT(*) FROM paiement WHERE status = 'termine'")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(montant) FROM paiement WHERE status = 'termine'")->fetchColumn(),
    'avg_votes_per_candidate' => $pdo->query("
        SELECT AVG(vote_count) FROM (
            SELECT COUNT(*) as vote_count 
            FROM paiement p 
            WHERE status = 'termine' 
            GROUP BY id_candidat
        ) as votes
    ")->fetchColumn(),
    'avg_revenue_per_vote' => $pdo->query("
        SELECT AVG(montant) FROM paiement WHERE status = 'termine'
    ")->fetchColumn()
];

// Statistiques par catégorie
$category_stats = $pdo->query("
    SELECT 
        c.categorie,
        COUNT(DISTINCT c.id) as total_candidates,
        COUNT(p.id) as total_votes,
        SUM(p.montant) as total_revenue
    FROM candidats c
    LEFT JOIN paiement p ON c.id = p.id_candidat AND p.status = 'termine'
    GROUP BY c.categorie
")->fetchAll();

// Top 10 des candidats
$top_candidates = $pdo->query("
    SELECT 
        c.*,
        COUNT(p.id) as vote_count,
        SUM(p.montant) as revenue
    FROM candidats c
    LEFT JOIN paiement p ON c.id = p.id_candidat AND p.status = 'termine'
    GROUP BY c.id
    ORDER BY vote_count DESC
    LIMIT 10
")->fetchAll();

// Statistiques par jour (30 derniers jours)
$daily_stats = $pdo->query("
    SELECT 
        DATE(dates) as date,
        COUNT(*) as transactions,
        SUM(montant) as revenue,
        COUNT(*) as votes
    FROM paiement
    WHERE status = 'termine'
    AND dates >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(dates)
    ORDER BY date DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-card {
            background: linear-gradient(45deg, #DAA520, #FFD700);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .progress {
            height: 10px;
            background-color: #f0f0f0;
        }
        .progress-bar {
            background-color: #DAA520;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Statistiques</h2>
                <div>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="export_excel" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </button>
                        <button type="submit" name="export_pdf" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistiques globales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($global_stats['total_votes']); ?></h4>
                        <p class="mb-0">Votes totaux</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($global_stats['total_revenue']); ?> FCFA</h4>
                        <p class="mb-0">Revenus totaux</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($global_stats['avg_votes_per_candidate'], 1); ?></h4>
                        <p class="mb-0">Moyenne des votes par candidat</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($global_stats['avg_revenue_per_vote']); ?> FCFA</h4>
                        <p class="mb-0">Revenu moyen par vote</p>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Répartition des votes par catégorie</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Évolution des votes</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="votesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 10 des candidats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Top 10 des candidats</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Votes</th>
                                    <th>Revenus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_candidates as $key => $candidate): ?>
                                    <tr>
                                        <td><?php echo $key + 1; ?></td>
                                        <td><?php echo escape($candidate['nom']); ?></td>
                                        <td><?php echo escape($candidate['categorie']); ?></td>
                                        <td><?php echo number_format($candidate['vote_count']); ?></td>
                                        <td><?php echo number_format($candidate['revenue']); ?> FCFA</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique des catégories
const categoryData = <?php echo json_encode($category_stats); ?>;
new Chart(document.getElementById('categoryChart'), {
    type: 'pie',
    data: {
        labels: categoryData.map(item => item.categorie),
        datasets: [{
            data: categoryData.map(item => item.total_votes),
            backgroundColor: ['#FF6384', '#36A2EB']
        }]
    }
});

// Graphique de l'évolution des votes
const dailyData = <?php echo json_encode($daily_stats); ?>;
new Chart(document.getElementById('votesChart'), {
    type: 'line',
    data: {
        labels: dailyData.map(item => item.date),
        datasets: [{
            label: 'Votes',
            data: dailyData.map(item => item.votes),
            borderColor: '#DAA520',
            fill: false
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>
