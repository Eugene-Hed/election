<?php
require_once '../includes/config.php';
require_once 'includes/auth.php';

checkAdmin();

// Suppression d'un gagnant
if (isset($_POST['supprimer'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("SELECT photo FROM anciens_gagnants WHERE id = ?");
    $stmt->execute([$id]);
    $photo = $stmt->fetchColumn();
    
    if ($photo && file_exists("../" . $photo)) {
        unlink("../" . $photo);
    }
    
    $stmt = $pdo->prepare("DELETE FROM anciens_gagnants WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = "Gagnant supprimé avec succès";
    header('Location: anciens-gagnants.php');
    exit();
}

// Modification d'un gagnant
if (isset($_POST['modifier'])) {
    $id = $_POST['id'];
    $nom = trim($_POST['nom']);
    $annee = (int)$_POST['annee'];
    $categorie = $_POST['categorie'];
    $description = trim($_POST['description']);
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $upload_dir = "../uploads/anciens_gagnants/";
        $filename = uniqid() . '_' . $_FILES['photo']['name'];
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename)) {
            $stmt = $pdo->prepare("SELECT photo FROM anciens_gagnants WHERE id = ?");
            $stmt->execute([$id]);
            $old_photo = $stmt->fetchColumn();
            if ($old_photo && file_exists("../" . $old_photo)) {
                unlink("../" . $old_photo);
            }
            
            $photo_url = "uploads/anciens_gagnants/" . $filename;
            $stmt = $pdo->prepare("UPDATE anciens_gagnants SET nom = ?, photo = ?, annee = ?, categorie = ?, description = ? WHERE id = ?");
            $stmt->execute([$nom, $photo_url, $annee, $categorie, $description, $id]);
        }
    } else {
        $stmt = $pdo->prepare("UPDATE anciens_gagnants SET nom = ?, annee = ?, categorie = ?, description = ? WHERE id = ?");
        $stmt->execute([$nom, $annee, $categorie, $description, $id]);
    }
    
    $_SESSION['success'] = "Gagnant modifié avec succès";
    header('Location: anciens-gagnants.php');
    exit();
}

// Ajout d'un nouveau gagnant
if (isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom']);
    $annee = (int)$_POST['annee'];
    $categorie = $_POST['categorie'];
    $description = trim($_POST['description']);

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $upload_dir = "../uploads/anciens_gagnants/";
        $filename = uniqid() . '_' . $_FILES['photo']['name'];
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename)) {
            $photo_url = "uploads/anciens_gagnants/" . $filename;
            
            $stmt = $pdo->prepare("INSERT INTO anciens_gagnants (nom, photo, annee, categorie, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $photo_url, $annee, $categorie, $description]);
            
            $_SESSION['success'] = "Gagnant ajouté avec succès";
            header('Location: anciens-gagnants.php');
            exit();
        }
    }
}

// Récupération des anciens gagnants
$gagnants = $pdo->query("SELECT * FROM anciens_gagnants ORDER BY annee DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anciens Gagnants - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gagnant-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .gagnant-card:hover {
            transform: translateY(-5px);
        }
        .gagnant-image {
            height: 300px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestion des Anciens Gagnants</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajoutModal">
                    <i class="fas fa-plus me-2"></i>Ajouter un gagnant
                </button>
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

            <div class="row">
                <?php foreach($gagnants as $gagnant): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card gagnant-card">
                            <img src="../<?php echo escape($gagnant['photo']); ?>" 
                                 class="gagnant-image" 
                                 alt="<?php echo escape($gagnant['nom']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo escape($gagnant['nom']); ?></h5>
                                <p class="card-text">
                                    <span class="badge bg-primary"><?php echo escape($gagnant['categorie']); ?></span>
                                    <span class="badge bg-secondary"><?php echo escape($gagnant['annee']); ?></span>
                                </p>
                                <p class="card-text"><?php echo escape($gagnant['description']); ?></p>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-warning" onclick="editGagnant(<?php echo htmlspecialchars(json_encode($gagnant)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce gagnant ?');">
                                        <input type="hidden" name="id" value="<?php echo $gagnant['id']; ?>">
                                        <button type="submit" name="supprimer" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="ajoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un ancien gagnant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" class="form-control" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Année</label>
                        <input type="number" class="form-control" name="annee" 
                               min="2000" max="<?php echo date('Y')-1; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="categorie" required>
                            <option value="Miss">Miss</option>
                            <option value="Master">Master</option>
                            <option value="Dauphine">Dauphine</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" class="form-control" name="photo" accept="image/*" required>
                    </div>
                    <button type="submit" name="ajouter" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modification -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier un gagnant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" class="form-control" name="nom" id="edit_nom" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Année</label>
                        <input type="number" class="form-control" name="annee" id="edit_annee"
                               min="2000" max="<?php echo date('Y')-1; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="categorie" id="edit_categorie" required>
                            <option value="Miss">Miss</option>
                            <option value="Master">Master</option>
                            <option value="Dauphine">Dauphine</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo (optionnel)</label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                    <button type="submit" name="modifier" class="btn btn-primary">Enregistrer les modifications</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editGagnant(gagnant) {
    document.getElementById('edit_id').value = gagnant.id;
    document.getElementById('edit_nom').value = gagnant.nom;
    document.getElementById('edit_annee').value = gagnant.annee;
    document.getElementById('edit_categorie').value = gagnant.categorie;
    document.getElementById('edit_description').value = gagnant.description;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

</body>
</html>
