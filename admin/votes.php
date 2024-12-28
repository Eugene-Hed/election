<?php
require_once '../includes/config.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Total des votes pour la pagination
$total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$total_pages = ceil($total_votes / $limit);

// Récupération des votes avec détails
$votes = $pdo->prepare("
    SELECT v.*, 
           c.name as candidate_name, 
           c.category,
           u.username as voter_name,
           u.email as voter_email,
           p.amount,
           p.payment_method,
           p.status as payment_status,
           p.transaction_reference
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.id
    JOIN users u ON v.voter_id = u.id
    JOIN payments p ON v.payment_id = p.id
    ORDER BY v.created_at DESC
    LIMIT ? OFFSET ?
");
$votes->execute([$limit, $offset]);
$votes = $votes->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Votes - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gold: #DAA520;
            --secondary-gold: #FFD700;
            --dark-gold: #B8860B;
        }
        
        .table th {
            background-color: var(--primary-gold);
            color: white;
        }
        
        .pagination .page-link {
            color: var(--dark-gold);
        }
        
        .pagination .active .page-link {
            background-color: var(--primary-gold);
            border-color: var(--primary-gold);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestion des Votes</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Exporter
                    </button>
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Rechercher...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="votesTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Votant</th>
                                    <th>Candidat</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Référence</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($votes as $vote): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($vote['created_at'])); ?></td>
                                        <td>
                                            <?php echo escape($vote['voter_name']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo escape($vote['voter_email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $vote['category'] === 'Miss' ? 'danger' : 'primary'; ?>">
                                                <?php echo escape($vote['category']); ?>
                                            </span>
                                            <?php echo escape($vote['candidate_name']); ?>
                                        </td>
                                        <td><?php echo number_format($vote['amount'], 2); ?> FCFA</td>
                                        <td><?php echo escape($vote['payment_method']); ?></td>
                                        <td>
                                            <small><?php echo escape($vote['transaction_reference']); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge bg-<?php 
                                                echo $vote['payment_status'] === 'Completed' ? 'success' : 
                                                    ($vote['payment_status'] === 'Pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo escape($vote['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" title="Détails"
                                                    onclick="showVoteDetails(<?php echo $vote['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>">Précédent</a>
                                </li>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>">Suivant</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails -->
<div class="modal fade" id="voteDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du vote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="voteDetailsContent">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Recherche en temps réel
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#votesTable tbody tr');

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Fonction pour afficher les détails d'un vote
function showVoteDetails(voteId) {
    // Ici, vous pouvez ajouter une requête AJAX pour charger les détails
    const modal = new bootstrap.Modal(document.getElementById('voteDetailsModal'));
    modal.show();
}

// Fonction pour exporter en Excel
function exportToExcel() {
    window.location.href = 'export-votes.php';
}
</script>

</body>
</html>
