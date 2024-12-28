<?php
require_once 'includes/config.php';

// Vérification de l'ID du candidat
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: candidates.php');
    exit();
}

$candidate_id = $_GET['id'];

// Récupération des informations du candidat
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch();

if (!$candidate) {
    header('Location: candidates.php');
    exit();
}

// Comptage des votes pour ce candidat
$stmt = $pdo->prepare("SELECT COUNT(*) as vote_count FROM votes WHERE candidate_id = ?");
$stmt->execute([$candidate_id]);
$votes = $stmt->fetch();

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Image du candidat -->
        <div class="col-md-6">
            <div class="card">
                <img src="<?php echo escape($candidate['photo_url']) ?: 'assets/images/default-candidate.jpg'; ?>" 
                     class="card-img-top" 
                     alt="<?php echo escape($candidate['name']); ?>"
                     style="height: 500px; object-fit: cover;">
            </div>
        </div>

        <!-- Informations du candidat -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title" style="color: #DAA520;">
                        <?php echo escape($candidate['name']); ?>
                    </h1>
                    <span class="badge mb-3" style="background-color: #DAA520;">
                        <?php echo escape($candidate['category']); ?>
                    </span>

                    <div class="mb-4">
                        <h4>Description</h4>
                        <p><?php echo nl2br(escape($candidate['description'])); ?></p>
                    </div>

                    <div class="mb-4">
                        <h4>Nombre de votes</h4>
                        <p class="h2" style="color: #DAA520;"><?php echo $votes['vote_count']; ?></p>
                    </div>

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="d-grid gap-2">
                            <a href="vote.php?candidate_id=<?php echo $candidate['id']; ?>" 
                               class="btn btn-lg" 
                               style="background-color: #DAA520; color: white;">
                                Voter pour <?php echo escape($candidate['name']); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Pour voter, veuillez vous 
                            <a href="login.php" style="color: #DAA520;">connecter</a> ou 
                            <a href="register.php" style="color: #DAA520;">créer un compte</a>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section partage social -->
            <div class="card mt-3">
                <div class="card-body">
                    <h4>Partager</h4>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           class="btn btn-primary" 
                           target="_blank">
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=Votez pour <?php echo urlencode($candidate['name']); ?>" 
                           class="btn btn-info text-white" 
                           target="_blank">
                            Twitter
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode('Votez pour ' . $candidate['name'] . ' ' . $_SERVER['REQUEST_URI']); ?>" 
                           class="btn btn-success" 
                           target="_blank">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton retour -->
    <div class="text-center mt-4">
        <a href="candidates.php" class="btn btn-outline-secondary">
            Retour à la liste des candidats
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
