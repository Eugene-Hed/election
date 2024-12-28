<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/cinetpay-config.php';

if (!isset($_GET['id'])) {
    header('Location: candidates.php');
    exit();
}

$candidate_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM candidats WHERE id = ?");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch();

if (!$candidate) {
    header('Location: candidates.php');
    exit();
}

$montant = 100;
$transaction_id = uniqid('VOTE_');
$description = "Vote pour " . $candidate['nom'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voter_name = $_POST['voter_name'];
    $voter_phone = $_POST['voter_phone'];
    
    $stmt = $pdo->prepare("INSERT INTO paiement (transaction_id, numvotant, montant, status, nbvote, id_candidat) VALUES (?, ?, ?, 'en attente', 1, ?)");
    $stmt->execute([$transaction_id, $voter_phone, $montant, $candidate_id]);
    $payment_id = $pdo->lastInsertId();
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="<?php echo escape($candidate['profil']); ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?php echo escape($candidate['nom']); ?>">
                        </div>
                        <div class="col-md-8">
                            <h3>Confirmation de vote</h3>
                            <p>Vous allez voter pour :</p>
                            <h4><?php echo escape($candidate['nom']); ?></h4>
                            <p>
                                <strong>Catégorie:</strong> <?php echo escape($candidate['categorie']); ?><br>
                                <strong>Spécialité:</strong> <?php echo escape($candidate['specialite']); ?><br>
                                <strong>Niveau:</strong> <?php echo escape($candidate['niveau']); ?>
                            </p>

                            <form id="voterForm" class="mb-3">
                                <div class="mb-3">
                                    <label class="form-label">Votre nom complet</label>
                                    <input type="text" class="form-control" id="voter_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Votre numéro de téléphone</label>
                                    <input type="tel" class="form-control" id="voter_phone" required 
                                           placeholder="Ex: 6XXXXXXXX">
                                </div>
                            </form>

                            <div class="alert alert-info">
                                <strong>Montant du vote:</strong> <?php echo number_format($montant); ?> FCFA
                            </div>

                            <button type="button" class="btn btn-primary" onclick="startPayment()">
                                Procéder au paiement
                            </button>
                            <a href="candidates.php" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.cinetpay.com/seamless/main.js"></script>
<script>
function startPayment() {
    const voterName = document.getElementById('voter_name').value.trim();
    const voterPhone = document.getElementById('voter_phone').value.trim();

    if (!voterName) {
        alert('Veuillez entrer votre nom');
        return;
    }
    if (!voterPhone) {
        alert('Veuillez entrer votre numéro de téléphone');
        return;
    }

    CinetPay.setConfig({
        apikey: '<?php echo CINETPAY_API_KEY; ?>',
        site_id: '<?php echo CINETPAY_SITE_ID; ?>',
        mode: 'SANDBOX',
        notify_url: '<?php echo CINETPAY_NOTIFY_URL; ?>'
    });

    CinetPay.getCheckout({
        transaction_id: '<?php echo $transaction_id; ?>',
        amount: <?php echo $montant; ?>,
        currency: 'XAF',
        channels: 'MOBILE_MONEY',
        description: '<?php echo addslashes($description); ?>',
        customer_name: voterName,
        customer_phone_number: voterPhone,
        customer_email: 'vote@isestma.com',
        customer_address: 'Yaoundé',
        customer_city: 'Yaoundé',
        customer_country: 'CM',
        customer_state: 'Centre',
        customer_zip_code: '00237',
        return_url: '<?php echo CINETPAY_RETURN_URL; ?>',
        cancel_url: '<?php echo CINETPAY_CANCEL_URL; ?>'
    });

    CinetPay.waitResponse(function(data) {
        if (data.status == "ACCEPTED") {
            window.location.href = "<?php echo CINETPAY_RETURN_URL; ?>?transaction_id=" + data.transaction_id;
        }
    });

    CinetPay.onError(function(data) {
        console.log('Erreur CinetPay:', data);
        alert("Une erreur est survenue lors du paiement. Veuillez réessayer.");
    });
}
</script>

<style>
.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
.btn-primary {
    background-color: #DAA520;
    border-color: #DAA520;
}
.btn-primary:hover {
    background-color: #B8860B;
    border-color: #B8860B;
}
.alert-info {
    background-color: #f8f9fa;
    border-color: #DAA520;
    color: #000;
}
.img-fluid {
    max-height: 300px;
    object-fit: cover;
}
</style>

<?php require_once 'includes/footer.php'; ?>
