<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['payment_id'])) {
    header('Location: candidates.php');
    exit();
}

$payment_id = $_GET['payment_id'];

// Récupération des informations de paiement
$stmt = $pdo->prepare("
    SELECT p.*, v.candidate_id, c.name as candidate_name, c.category 
    FROM payments p
    JOIN votes v ON v.payment_id = p.id
    JOIN candidates c ON v.candidate_id = c.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->execute([$payment_id, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: dashboard-voter.php');
    exit();
}

if (isset($_POST['confirm_payment'])) {
    $phone = $_POST['phone'];
    $operator = $_POST['operator'];
    
    // Mise à jour du paiement avec les informations de l'opérateur
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET payment_method = ?, 
            phone_number = ?,
            status = 'Processing'
        WHERE id = ?
    ");
    $stmt->execute([$operator, $phone, $payment_id]);
    
    // Redirection vers la page de confirmation
    header('Location: payment-confirmation.php?payment_id=' . $payment_id);
    exit();
}

require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - ISESTMA</title>
    <style>
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .operator-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .operator-option:hover {
            border-color: #1a237e;
            background-color: rgba(26, 35, 126, 0.05);
        }
        .operator-option.selected {
            border-color: #1a237e;
            background-color: rgba(26, 35, 126, 0.1);
        }
        .operator-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="payment-container">
        <div class="card shadow-sm">
            <div class="card-header bg-custom-gradient text-white">
                <h3 class="mb-0">Paiement du vote</h3>
            </div>
            
            <div class="card-body">
                <!-- Résumé de la transaction -->
                <div class="alert alert-info mb-4">
                    <h5>Résumé de votre vote</h5>
                    <p class="mb-1">Candidat: <?php echo escape($payment['candidate_name']); ?></p>
                    <p class="mb-1">Catégorie: <?php echo escape($payment['category']); ?></p>
                    <p class="mb-0">Montant: <?php echo number_format($payment['amount'], 2); ?> FCFA</p>
                </div>

                <form method="POST" action="" id="paymentForm">
                    <h5 class="mb-3">Choisissez votre opérateur</h5>

                    <!-- Option Orange Money -->
                    <div class="operator-option" onclick="selectOperator('Orange Money')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="operator" 
                                   value="Orange Money" id="orangeOption" required>
                            <label class="form-check-label d-flex align-items-center" for="orangeOption">
                                <img src="assets/images/orange-money.png" alt="Orange Money" class="operator-logo me-3">
                                <div>
                                    <h6 class="mb-0">Orange Money</h6>
                                    <small class="text-muted">Paiement via Orange Money</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Option MTN Mobile Money -->
                    <div class="operator-option" onclick="selectOperator('MTN Mobile Money')">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="operator" 
                                   value="MTN Mobile Money" id="mtnOption" required>
                            <label class="form-check-label d-flex align-items-center" for="mtnOption">
                                <img src="assets/images/mtn-momo.png" alt="MTN Mobile Money" class="operator-logo me-3">
                                <div>
                                    <h6 class="mb-0">MTN Mobile Money</h6>
                                    <small class="text-muted">Paiement via MTN Mobile Money</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Numéro de téléphone -->
                    <div class="mb-4 mt-4">
                        <label for="phone" class="form-label">Numéro de téléphone</label>
                        <div class="input-group">
                            <span class="input-group-text">+237</span>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   pattern="[6-9][0-9]{8}" maxlength="9" required
                                   placeholder="6XXXXXXXX">
                        </div>
                        <small class="text-muted">Format: 6XXXXXXXX</small>
                    </div>

                    <!-- Instructions de paiement -->
                    <div class="alert alert-warning mb-4">
                        <h6><i class="fas fa-info-circle"></i> Instructions:</h6>
                        <ol class="mb-0">
                            <li>Entrez votre numéro de téléphone</li>
                            <li>Vous recevrez un message de confirmation sur votre téléphone</li>
                            <li>Validez le paiement avec votre code secret</li>
                        </ol>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="confirm_payment" class="btn btn-custom btn-lg">
                            Procéder au paiement
                        </button>
                        <a href="dashboard-voter.php" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function selectOperator(operator) {
    document.querySelectorAll('.operator-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    const selectedOption = document.querySelector(`input[value="${operator}"]`);
    if (selectedOption) {
        selectedOption.checked = true;
        selectedOption.closest('.operator-option').classList.add('selected');
    }
}

// Validation du numéro de téléphone
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 9) value = value.slice(0, 9);
    e.target.value = value;
});
</script>

<?php require_once 'includes/footer.php'; ?>
