<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Vérification si l'utilisateur a déjà voté
$stmt = $pdo->prepare("SELECT COUNT(*) as has_voted FROM votes WHERE voter_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$voteStatus = $stmt->fetch();
$hasVoted = $voteStatus['has_voted'] > 0;

// Récupération du vote de l'utilisateur s'il existe
$stmt = $pdo->prepare("
    SELECT v.*, c.name as candidate_name, c.category, c.photo_url, p.amount, p.payment_method, p.status
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.id
    JOIN payments p ON v.payment_id = p.id
    WHERE v.voter_id = ?
    ORDER BY v.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$vote = $stmt->fetch();

require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1a237e;
            --secondary-gold: #DAA520;
            --gradient-start: #1a237e;
            --gradient-end: #3949ab;
        }

        .bg-custom-gradient {
            background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end));
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--primary-blue), var(--secondary-gold));
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .status-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .profile-stats {
            background: rgba(26, 35, 126, 0.05);
            border-radius: 10px;
            padding: 20px;
        }

        .vote-details {
            border-left: 4px solid var(--secondary-gold);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <!-- Bannière de bienvenue -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-custom-gradient">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="display-4 mb-0">Bienvenue, <?php echo escape($user['username']); ?></h2>
                            <p class="lead mb-0">Votre espace personnel ISESTMA</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="h5 mb-0">Membre depuis: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                            <small><?php echo escape($user['email']); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statut du vote -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center p-4">
                    <?php if($hasVoted): ?>
                        <i class="fas fa-check-circle status-icon text-success"></i>
                        <h3 class="text-success">Vote effectué avec succès !</h3>
                        <p class="lead">Vous avez voté pour <?php echo escape($vote['candidate_name']); ?></p>
                    <?php else: ?>
                        <i class="fas fa-vote-yea status-icon" style="color: var(--primary-blue);"></i>
                        <h3 class="text-primary">Vous n'avez pas encore voté</h3>
                        <p class="lead mb-4">Participez à l'élection Miss & Master ISESTMA</p>
                        <a href="candidates.php" class="btn btn-custom btn-lg px-5">
                            Voter maintenant
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if($hasVoted): ?>
    <!-- Détails du vote -->
    <div class="row">
        <div class="col-md-8">
            <div class="card h-100 vote-details">
                <div class="card-header bg-custom-gradient text-white">
                    <h4 class="mb-0">Détails de votre vote</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <img src="<?php echo escape($vote['photo_url']); ?>" 
                             alt="<?php echo escape($vote['candidate_name']); ?>"
                             class="rounded-circle me-3"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <h5 class="mb-1"><?php echo escape($vote['candidate_name']); ?></h5>
                            <p class="text-muted mb-0">Catégorie: <?php echo escape($vote['category']); ?></p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="profile-stats">
                                <h6 class="text-primary mb-2">Date du vote</h6>
                                <p class="mb-0"><?php echo date('d/m/Y H:i', strtotime($vote['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-stats">
                                <h6 class="text-primary mb-2">Montant</h6>
                                <p class="mb-0"><?php echo number_format($vote['amount'], 2); ?> FCFA</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-stats">
                                <h6 class="text-primary mb-2">Méthode de paiement</h6>
                                <p class="mb-0"><?php echo escape($vote['payment_method']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-stats">
                                <h6 class="text-primary mb-2">Statut du paiement</h6>
                                <span class="badge bg-<?php echo $vote['status'] === 'Completed' ? 'success' : 
                                    ($vote['status'] === 'Pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo escape($vote['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h4 class="mb-0 text-primary">Informations</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 text-primary"></i>
                            Vote unique par compte
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-calendar-alt me-3 text-primary"></i>
                            Résultats le 31/12/2024
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-share-alt me-3 text-primary"></i>
                            Partagez l'événement
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php require_once 'includes/footer.php'; ?>
