<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Récupération des résultats Miss
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(p.id) as total_votes
    FROM candidats c
    LEFT JOIN paiement p ON c.id = p.id_candidat AND p.status = 'termine'
    WHERE c.categorie = 'Miss'
    GROUP BY c.id
    ORDER BY total_votes DESC
");
$stmt->execute();
$miss_results = $stmt->fetchAll();

// Récupération des résultats Master
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(p.id) as total_votes
    FROM candidats c
    LEFT JOIN paiement p ON c.id = p.id_candidat AND p.status = 'termine'
    WHERE c.categorie = 'Master'
    GROUP BY c.id
    ORDER BY total_votes DESC
");
$stmt->execute();
$master_results = $stmt->fetchAll();

// Calcul du total des votes pour les pourcentages
$total_votes_miss = array_sum(array_column($miss_results, 'total_votes'));
$total_votes_master = array_sum(array_column($master_results, 'total_votes'));
?>

<div class="container py-5">
    <h1 class="text-center mb-5" style="color: #DAA520;">Résultats des Votes</h1>

    <!-- Résultats Miss -->
    <div class="mb-5">
        <h2 class="text-center mb-4">Catégorie Miss</h2>
        <div class="row">
            <?php foreach($miss_results as $index => $candidate): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="position-relative">
                            <?php if($index < 3): ?>
                                <div class="position-absolute top-0 start-0 p-2">
                                    <span class="badge bg-<?php echo $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'bronze'); ?>">
                                        <?php echo $index + 1; ?>e
                                    </span>
                                </div>
                            <?php endif; ?>
                            <img src="<?php echo escape($candidate['profil']); ?>" 
                                 class="card-img-top" style="height: 300px; object-fit: cover;"
                                 alt="<?php echo escape($candidate['nom']); ?>">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo escape($candidate['nom']); ?></h5>
                            <p class="card-text">
                                <strong>Votes:</strong> <?php echo $candidate['total_votes']; ?><br>
                                <strong>Pourcentage:</strong> 
                                <?php echo $total_votes_miss > 0 ? round(($candidate['total_votes'] / $total_votes_miss) * 100, 2) : 0; ?>%
                            </p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo $total_votes_miss > 0 ? ($candidate['total_votes'] / $total_votes_miss) * 100 : 0; ?>%; 
                                            background-color: #DAA520;" 
                                     aria-valuenow="<?php echo $candidate['total_votes']; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="<?php echo $total_votes_miss; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Résultats Master -->
    <div class="mb-5">
        <h2 class="text-center mb-4">Catégorie Master</h2>
        <div class="row">
            <?php foreach($master_results as $index => $candidate): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="position-relative">
                            <?php if($index < 3): ?>
                                <div class="position-absolute top-0 start-0 p-2">
                                    <span class="badge bg-<?php echo $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'bronze'); ?>">
                                        <?php echo $index + 1; ?>e
                                    </span>
                                </div>
                            <?php endif; ?>
                            <img src="<?php echo escape($candidate['profil']); ?>" 
                                 class="card-img-top" style="height: 300px; object-fit: cover;"
                                 alt="<?php echo escape($candidate['nom']); ?>">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo escape($candidate['nom']); ?></h5>
                            <p class="card-text">
                                <strong>Votes:</strong> <?php echo $candidate['total_votes']; ?><br>
                                <strong>Pourcentage:</strong> 
                                <?php echo $total_votes_master > 0 ? round(($candidate['total_votes'] / $total_votes_master) * 100, 2) : 0; ?>%
                            </p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo $total_votes_master > 0 ? ($candidate['total_votes'] / $total_votes_master) * 100 : 0; ?>%; 
                                            background-color: #DAA520;" 
                                     aria-valuenow="<?php echo $candidate['total_votes']; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="<?php echo $total_votes_master; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.bg-bronze {
    background-color: #CD7F32;
}
.progress {
    height: 10px;
    margin-top: 10px;
}
.card {
    transition: transform 0.3s;
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
.card:hover {
    transform: translateY(-5px);
}
</style>

<?php require_once 'includes/footer.php'; ?>
