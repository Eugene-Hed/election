<?php
require_once '../includes/config.php';
require_once 'includes/auth.php';

checkAdmin();

// Gestion du changement de statut
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE paiement SET status = ? WHERE transaction_id = ?");
    $stmt->execute([$_POST['status'], $_POST['transaction_id']]);

    if ($_POST['status'] === 'termine') {
        $stmt = $pdo->prepare("
            UPDATE candidats 
            SET votes = votes + 1 
            WHERE id = (SELECT id_candidat FROM paiement WHERE transaction_id = ?)
        ");
        $stmt->execute([$_POST['transaction_id']]);
    }

    $_SESSION['success'] = "Statut du paiement mis à jour";
    header('Location: payments.php');
    exit();
}

// Statistiques
$stats = [
    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM paiement WHERE status = 'termine'")->fetchColumn(),
    'pending_amount' => $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM paiement WHERE status = 'en attente'")->fetchColumn(),
    'total_transactions' => $pdo->query("SELECT COUNT(*) FROM paiement")->fetchColumn(),
    'success_rate' => $pdo->query("
        SELECT (COUNT(CASE WHEN status = 'termine' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0))
        FROM paiement
    ")->fetchColumn()
];

// Récupération des paiements
$payments = $pdo->query("
    SELECT p.*, c.nom as nom_candidat, c.categorie
    FROM paiement p
    JOIN candidats c ON p.id_candidat = c.id
    ORDER BY p.dates DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Paiements - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-card {
            background: linear-gradient(45deg, #DAA520, #FFD700);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #DAA520;
            color: white;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <h2 class="mb-4">Gestion des Paiements</h2>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['total_revenue']); ?> FCFA</h4>
                        <p class="mb-0">Revenus totaux</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['pending_amount']); ?> FCFA</h4>
                        <p class="mb-0">En attente</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['total_transactions']); ?></h4>
                        <p class="mb-0">Transactions</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['success_rate'], 1); ?>%</h4>
                        <p class="mb-0">Taux de succès</p>
                    </div>
                </div>
            </div>

            <!-- Liste des paiements -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Numero votant</th>
                                    <th>Date</th>
                                    <th>Candidat</th>
                                    <th>Catégorie</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo escape($payment['transaction_id']); ?></td>
                                        <td><?php echo escape($payment['numvotant']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($payment['dates'])); ?></td>
                                        <td><?php echo escape($payment['nom_candidat']); ?></td>
                                        <td><?php echo escape($payment['categorie']); ?></td>
                                        <td><?php echo number_format($payment['montant']); ?> FCFA</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $payment['status'] === 'termine' ? 'success' : 
                                                    ($payment['status'] === 'en attente' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo escape($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($payment['status'] === 'en attente'): ?>
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['transaction_id']; ?>">
                                                    <input type="hidden" name="status" value="termine">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-success" title="Valider">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="transaction_id" value="<?php echo $payment['transaction_id']; ?>">
                                                    <input type="hidden" name="status" value="echec">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-danger" title="Refuser">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
