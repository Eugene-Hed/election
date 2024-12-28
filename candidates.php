<?php
require_once 'includes/config.php';

// Récupération des candidats
$stmt = $pdo->prepare("SELECT * FROM candidats ORDER BY categorie, nom");
$stmt->execute();
$candidates = $stmt->fetchAll();

// Séparation des candidats par catégorie
$miss_candidates = array_filter($candidates, function($candidate) {
    return $candidate['categorie'] === 'Miss';
});

$master_candidates = array_filter($candidates, function($candidate) {
    return $candidate['categorie'] === 'Master';
});

require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5" style="color: #DAA520;">Nos Candidats</h1>

    <!-- Section Miss -->
    <h2 class="text-center mb-4">Candidates Miss ISESTMA</h2>
    <div class="row g-4 mb-5">
        <?php foreach($miss_candidates as $candidate): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="position-relative">
                        <img src="<?php echo escape($candidate['profil']) ?: 'assets/images/default-candidate.jpg'; ?>" 
                             class="card-img-top" alt="<?php echo escape($candidate['nom']); ?>"
                             style="height: 300px; object-fit: cover;">
                        <span class="position-absolute top-0 end-0 badge bg-primary m-2">
                            <?php echo $candidate['votes']; ?> votes
                        </span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title" style="color: #DAA520;"><?php echo escape($candidate['nom']); ?></h5>
                        <p class="card-text">
                            <strong>Spécialité:</strong> <?php echo escape($candidate['specialite']); ?><br>
                            <strong>Niveau:</strong> <?php echo escape($candidate['niveau']); ?>
                        </p>
                        <p class="card-text"><?php echo escape($candidate['message']); ?></p>
                        <div class="text-center">
                            <a href="vote.php?id=<?php echo $candidate['id']; ?>" 
                               class="btn" style="background-color: #DAA520; color: white;">
                                Voter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Section Master -->
    <h2 class="text-center mb-4">Candidats Master ISESTMA</h2>
    <div class="row g-4">
        <?php foreach($master_candidates as $candidate): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="position-relative">
                        <img src="<?php echo escape($candidate['profil']) ?: 'assets/images/default-candidate.jpg'; ?>" 
                             class="card-img-top" alt="<?php echo escape($candidate['nom']); ?>"
                             style="height: 300px; object-fit: cover;">
                        <span class="position-absolute top-0 end-0 badge bg-primary m-2">
                            <?php echo $candidate['votes']; ?> votes
                        </span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title" style="color: #DAA520;"><?php echo escape($candidate['nom']); ?></h5>
                        <p class="card-text">
                            <strong>Spécialité:</strong> <?php echo escape($candidate['specialite']); ?><br>
                            <strong>Niveau:</strong> <?php echo escape($candidate['niveau']); ?>
                        </p>
                        <p class="card-text"><?php echo escape($candidate['message']); ?></p>
                        <div class="text-center">
                            <a href="vote.php?id=<?php echo $candidate['id']; ?>" 
                               class="btn" style="background-color: #DAA520; color: white;">
                                Voter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-5">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Le vote coûte 100 FCFA. Paiement par Mobile Money uniquement.
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
