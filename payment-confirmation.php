<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['payment_id'])) {
    header('Location: candidates.php');
    exit();
}

$payment_id = $_GET['payment_id'];

// Récupération des informations de paiement
$stmt = $pdo->prepare("
    SELECT p.*, v.candidate_id, c.name as candidate_name, c.category, u.username
    FROM payments p
    JOIN votes v ON v.payment_id = p.id
    JOIN candidates c ON v.candidate_id = c.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->execute([$payment_id, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: dashboard-voter.php');
    exit();
}

require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Paiement - ISESTMA</title>
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .status-icon {
            font-size: 4rem;
            color: #1a237e;
            margin-bottom: 1rem;
        }
        .transaction-details {
            background: rgba(26, 35, 126, 0.05);
            border-radius: 10px;
            padding: 20px;
        }
        .timer {
            font-size: 2rem;
            color: #1a237e;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="confirmation-container">
        <div class="card shadow-sm">
            <div class="card-body text-center p-5">
                <i class="fas fa-mobile-alt status-icon"></i>
                <h2 class="mb-4">Confirmation en attente</h2>
                
                <div class="timer mb-4">
                    <span id="countdown">05:00</span>
                </div>

                <div class="alert alert-info mb-4">
                    <p class="mb-0">
                        Veuillez confirmer le paiement sur votre téléphone
                        <strong><?php echo escape($payment['phone_number']); ?></strong>
                    </p>
                </div>

                <div class="transaction-details text-start mb-4">
                    <h5 class="mb-3">Détails de la transaction</h5>
                    <p class="mb-2"><strong>Référence:</strong> <?php echo escape($payment['transaction_reference']); ?></p>
                    <p class="mb-2"><strong>Montant:</strong> <?php echo number_format($payment['amount'], 2); ?> FCFA</p>
                    <p class="mb-2"><strong>Opérateur:</strong> <?php echo escape($payment['payment_method']); ?></p>
                    <p class="mb-2"><strong>Candidat:</strong> <?php echo escape($payment['candidate_name']); ?></p>
                    <p class="mb-0"><strong>Catégorie:</strong> <?php echo escape($payment['category']); ?></p>
                </div>

                <div class="alert alert-warning">
                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Instructions:</h6>
                    <ol class="text-start mb-0">
                        <li>Vous allez recevoir une notification sur votre téléphone</li>
                        <li>Entrez votre code secret pour valider le paiement</li>
                        <li>Une fois validé, vous serez redirigé automatiquement</li>
                    </ol>
                </div>

                <div class="mt-4">
                    <a href="dashboard-voter.php" class="btn btn-outline-secondary">
                        Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Compte à rebours
function startCountdown(duration, display) {
    let timer = duration, minutes, seconds;
    let countdown = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(countdown);
            window.location.href = 'dashboard-voter.php';
        }
    }, 1000);
}

// Vérification du statut du paiement
function checkPaymentStatus() {
    fetch('check-payment-status.php?payment_id=<?php echo $payment_id; ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'Completed') {
                window.location.href = 'payment-success.php?payment_id=<?php echo $payment_id; ?>';
            }
        });
}

// Initialisation
window.onload = function () {
    let fiveMinutes = 60 * 5,
        display = document.querySelector('#countdown');
    startCountdown(fiveMinutes, display);
    
    // Vérification toutes les 5 secondes
    setInterval(checkPaymentStatus, 5000);
};
</script>

<?php require_once 'includes/footer.php'; ?>
