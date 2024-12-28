<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des votes de l'utilisateur avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT v.*, c.name as candidate_name, c.category, c.photo_url, 
           p.amount, p.payment_method, p.status, p.transaction_reference
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.id
    JOIN payments p ON v.payment_id = p.id
    WHERE v.voter_id = ?
    ORDER BY v.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$_SESSION['user_id'], $limit, $offset]);
$votes = $stmt->fetchAll();

// Compte total des votes pour la pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE voter_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_votes = $stmt->fetchColumn();
$total_pages = ceil($total_votes / $limit);

require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Votes - ISESTMA</title>
    <style>
        .vote-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .vote-card:hover {
            transform: translateY(-5px);
        }
        .candidate-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .vote-details {
            background: rgba(26, 35, 126, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-custom-gradient text-white">
                <div class="card-body">
                    <h2 class="mb-0">Historique de mes votes</h2>
                    <p class="mb-0">Total des votes: <?php echo $total_votes; ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($votes) > 0): ?>
        <?php foreach($votes as $vote): ?>
            <div class="card vote-card shadow-sm">
                <div class="card-body">
                    <span class="status-badge badge bg-<?php echo $vote['status'] === 'Completed' ? 'success' : 
                        ($vote['status'] === 'Pending' ? 'warning' : 'danger'); ?>">
                        <?php echo escape($vote['status']); ?>
                    </span>

                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="<?php echo escape($vote['photo_url']); ?>" 
                                 alt="<?php echo escape($vote['candidate_name']); ?>"
                                 class="candidate-image mb-2">
                        </div>
                        <div class="col-md-5">
                            <h4 class="mb-2"><?php echo escape($vote['candidate_name']); ?></h4>
                            <p class="mb-1"><strong>Catégorie:</strong> <?php echo escape($vote['category']); ?></p>
                            <p class="mb-0"><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($vote['created_at'])); ?></p>
                        </div>
                        <div class="col-md-5">
                            <div class="vote-details">
                                <p class="mb-1"><strong>Référence:</strong> <?php echo escape($vote['transaction_reference']); ?></p>
                                <p class="mb-1"><strong>Montant:</strong> <?php echo number_format($vote['amount'], 2); ?> FCFA</p>
                                <p class="mb-0"><strong>Méthode:</strong> <?php echo escape($vote['payment_method']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <nav aria-label="Navigation des pages" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h4>Aucun vote effectué</h4>
            <p class="mb-0">Vous n'avez pas encore voté pour un candidat.</p>
            <a href="candidates.php" class="btn btn-custom mt-3">Voter maintenant</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
