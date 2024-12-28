<?php
require_once '../includes/config.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Gestion de la suppression
if (isset($_POST['delete_user'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_POST['user_id']]);
    $_SESSION['success'] = "Utilisateur supprimé avec succès";
    header('Location: users.php');
    exit();
}

// Gestion du changement de rôle
if (isset($_POST['change_role'])) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_POST['new_role'], $_POST['user_id']]);
    $_SESSION['success'] = "Rôle modifié avec succès";
    header('Location: users.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Filtres
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête avec filtres
$query = "SELECT u.*, 
          COUNT(DISTINCT v.id) as vote_count,
          COALESCE(SUM(p.amount), 0) as total_spent,
          MAX(v.created_at) as last_vote_date
          FROM users u
          LEFT JOIN votes v ON u.id = v.voter_id
          LEFT JOIN payments p ON v.payment_id = p.id AND p.status = 'Completed'
          WHERE 1=1";

$params = [];

if ($role_filter) {
    $query .= " AND u.role = ?";
    $params[] = $role_filter;
}

if ($search) {
    $query .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Total des utilisateurs pour la pagination
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Statistiques globales
$stats = [
    'total_users' => $total_users,
    'active_voters' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'voter'")->fetchColumn(),
    'voted_users' => $pdo->query("SELECT COUNT(DISTINCT voter_id) FROM votes")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'Completed'")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gold: #DAA520;
            --secondary-gold: #FFD700;
            --dark-gold: #B8860B;
        }
        
        .stats-card {
            background: linear-gradient(45deg, var(--primary-gold), var(--secondary-gold));
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .table th {
            background-color: var(--primary-gold);
            color: white;
        }
        
        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <!-- En-tête avec titre et actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestion des Utilisateurs</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportUsers()">
                        <i class="fas fa-file-excel me-2"></i>Exporter
                    </button>
                </div>
            </div>

            <!-- Messages de succès -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['total_users']); ?></h4>
                        <p class="mb-0">Utilisateurs totaux</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['active_voters']); ?></h4>
                        <p class="mb-0">Votants actifs</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['voted_users']); ?></h4>
                        <p class="mb-0">Ont déjà voté</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h4><?php echo number_format($stats['total_revenue']); ?> FCFA</h4>
                        <p class="mb-0">Revenus totaux</p>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select name="role" class="form-select">
                                <option value="">Tous les rôles</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="voter" <?php echo $role_filter === 'voter' ? 'selected' : ''; ?>>Votant</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Rechercher par nom ou email..."
                                   value="<?php echo escape($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Votes</th>
                                    <th>Dépenses</th>
                                    <th>Dernier vote</th>
                                    <th>Inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                    <tr>
                                        <td>
                                            <?php echo escape($user['username']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo escape($user['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="role-badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                                <?php echo ucfirst(escape($user['role'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['vote_count']; ?></td>
                                        <td><?php echo number_format($user['total_spent'], 2); ?> FCFA</td>
                                        <td>
                                            <?php echo $user['last_vote_date'] ? date('d/m/Y H:i', strtotime($user['last_vote_date'])) : '-'; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info action-btn" 
                                                        onclick="showUserDetails(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($user['role'] !== 'admin'): ?>
                                                    <button class="btn btn-sm btn-warning action-btn"
                                                            onclick="changeRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">
                                                        <i class="fas fa-user-edit"></i>
                                                    </button>
                                                    <form action="" method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger action-btn">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
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
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&role=<?php echo $role_filter; ?>&search=<?php echo $search; ?>">Précédent</a>
                                </li>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $role_filter; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&role=<?php echo $role_filter; ?>&search=<?php echo $search; ?>">Suivant</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails Utilisateur -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Changement de Rôle -->
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Changer le rôle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="roleUserId">
                    <select name="new_role" class="form-select">
                        <option value="voter">Votant</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="change_role" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fonction pour afficher les détails d'un utilisateur
function showUserDetails(userId) {
    fetch(`get-user-details.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('userDetailsContent');
            content.innerHTML = `
                <div class="user-details">
                    <h6>Informations personnelles</h6>
                    <p><strong>Nom:</strong> ${data.username}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Date d'inscription:</strong> ${data.created_at}</p>
                    
                    <h6 class="mt-4">Statistiques</h6>
                    <p><strong>Nombre de votes:</strong> ${data.vote_count}</p>
                    <p><strong>Total dépensé:</strong> ${data.total_spent} FCFA</p>
                    <p><strong>Dernier vote:</strong> ${data.last_vote_date || 'Aucun vote'}</p>
                    
                    <h6 class="mt-4">Dernières activités</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.activities.map(activity => `
                                    <tr>
                                        <td>${activity.date}</td>
                                        <td>${activity.action}</td>
                                        <td>${activity.details}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
        })
        .catch(error => console.error('Erreur:', error));
}

// Fonction pour changer le rôle
function changeRole(userId, currentRole) {
    document.getElementById('roleUserId').value = userId;
    const modal = new bootstrap.Modal(document.getElementById('changeRoleModal'));
    modal.show();
}

// Fonction pour exporter les utilisateurs
function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `export-users.php?role=${params.get('role') || ''}&search=${params.get('search') || ''}`;
}

// Initialisation des tooltips Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

</body>
</html>
