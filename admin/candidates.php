<?php
require_once '../includes/config.php';
require_once 'includes/auth.php';

checkAdmin();

// Gestion de la suppression
if (isset($_POST['delete_candidate'])) {
    $stmt = $pdo->prepare("DELETE FROM candidats WHERE id = ?");
    $stmt->execute([$_POST['candidate_id']]);
    $_SESSION['success'] = "Candidat supprimé avec succès";
    header('Location: candidates.php');
    exit();
}

// Récupération des candidats avec statistiques
$candidates = $pdo->query("
    SELECT c.*, 
           (SELECT SUM(montant) FROM paiement WHERE id_candidat = c.id AND status = 'termine') as total_revenue
    FROM candidats c
    ORDER BY c.categorie, c.nom
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Candidats - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gold: #DAA520;
            --secondary-gold: #FFD700;
            --dark-gold: #B8860B;
        }

        .candidate-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .candidate-card:hover {
            transform: translateY(-5px);
        }

        .candidate-image {
            height: 300px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
            background-color: #f8f9fa;
        }

        .stats-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(218, 165, 32, 0.9);
            color: white;
        }

        .category-badge {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .action-buttons {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .candidate-card:hover .action-buttons {
            opacity: 1;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestion des Candidats</h2>
                <a href="add-candidate.php" class="btn" style="background-color: var(--primary-gold); color: white;">
                    <i class="fas fa-plus me-2"></i>Ajouter un candidat
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <select class="form-select" id="categoryFilter">
                                <option value="">Toutes les catégories</option>
                                <option value="Miss">Miss</option>
                                <option value="Master">Master</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchInput" 
                                   placeholder="Rechercher un candidat...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="sortBy">
                                <option value="name">Nom</option>
                                <option value="votes">Nombre de votes</option>
                                <option value="revenue">Revenus générés</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des candidats -->
            <div class="row" id="candidatesList">
                <?php foreach($candidates as $candidate): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card candidate-card">
                            <img src="../<?php echo escape($candidate['profil']); ?>" 
                                 class="candidate-image" 
                                 alt="<?php echo escape($candidate['nom']); ?>"
                                 onerror="this.src='../assets/images/default-candidate.jpg'">
                            
                            <span class="badge category-badge bg-<?php echo $candidate['categorie'] === 'Miss' ? 'danger' : 'primary'; ?>">
                                <?php echo escape($candidate['categorie']); ?>
                            </span>
                            
                            <span class="badge stats-badge">
                                <?php echo $candidate['votes']; ?> votes
                            </span>

                            <div class="card-body">
                                <h5 class="card-title"><?php echo escape($candidate['nom']); ?></h5>
                                <p class="card-text">
                                    <strong>Spécialité:</strong> <?php echo escape($candidate['specialite']); ?><br>
                                    <strong>Niveau:</strong> <?php echo escape($candidate['niveau']); ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Revenus: <?php echo number_format($candidate['total_revenue'] ?? 0); ?> FCFA
                                    </small>
                                    <div class="action-buttons">
                                        <a href="edit-candidate.php?id=<?php echo $candidate['id']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce candidat ?');">
                                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                            <button type="submit" name="delete_candidate" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const searchInput = document.getElementById('searchInput');
    const sortBy = document.getElementById('sortBy');
    const candidatesList = document.getElementById('candidatesList');

    function filterAndSortCandidates() {
        const cards = Array.from(candidatesList.getElementsByClassName('col-md-4'));
        const searchTerm = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        const sortValue = sortBy.value;

        cards.forEach(card => {
            const candidateName = card.querySelector('.card-title').textContent.toLowerCase();
            const candidateCategory = card.querySelector('.category-badge').textContent.trim();
            
            const matchesSearch = candidateName.includes(searchTerm);
            const matchesCategory = !category || candidateCategory === category;

            card.style.display = matchesSearch && matchesCategory ? '' : 'none';
        });

        // Tri des cartes
        cards.sort((a, b) => {
            if (sortValue === 'name') {
                return a.querySelector('.card-title').textContent.localeCompare(
                    b.querySelector('.card-title').textContent
                );
            } else if (sortValue === 'votes') {
                return b.querySelector('.stats-badge').textContent.match(/\d+/) - 
                       a.querySelector('.stats-badge').textContent.match(/\d+/);
            }
            return 0;
        });

        cards.forEach(card => candidatesList.appendChild(card));
    }

    categoryFilter.addEventListener('change', filterAndSortCandidates);
    searchInput.addEventListener('input', filterAndSortCandidates);
    sortBy.addEventListener('change', filterAndSortCandidates);
});
</script>

</body>
</html>
