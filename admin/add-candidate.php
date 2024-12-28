<?php
require_once '../includes/config.php';
require_once 'includes/auth.php';

checkAdmin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $specialite = trim($_POST['specialite']);
    $niveau = (int)$_POST['niveau'];
    $message = trim($_POST['message']);
    $categorie = $_POST['categorie'];
    
    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom est requis";
    }
    if (empty($specialite)) {
        $errors[] = "La spécialité est requise";
    }
    if ($niveau < 1 || $niveau > 5) {
        $errors[] = "Le niveau doit être entre 1 et 5";
    }
    if (!in_array($categorie, ['Miss', 'Master'])) {
        $errors[] = "Catégorie invalide";
    }

    // Gestion de l'upload de photo
    if (isset($_FILES['profil']) && $_FILES['profil']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profil']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($filetype, $allowed)) {
            $errors[] = "Type de fichier non autorisé. Utilisez JPG, JPEG, PNG ou GIF";
        } else {
            $newname = uniqid() . "." . $filetype;
            $upload_dir = "../uploads/candidates/";
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profil']['tmp_name'], $upload_dir . $newname)) {
                $profil_url = "uploads/candidates/" . $newname;
            } else {
                $errors[] = "Erreur lors de l'upload de l'image";
            }
        }
    } else {
        $errors[] = "La photo est requise";
    }

    // Si pas d'erreurs, insertion en base de données
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO candidats (nom, specialite, niveau, message, profil, categorie, votes) 
                VALUES (?, ?, ?, ?, ?, ?, 0)
            ");
            
            if ($stmt->execute([$nom, $specialite, $niveau, $message, $profil_url, $categorie])) {
                $_SESSION['success'] = "Candidat ajouté avec succès";
                header('Location: candidates.php');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout du candidat: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Candidat - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            display: none;
        }
        .custom-file-upload {
            border: 2px dashed #DAA520;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .custom-file-upload:hover {
            background-color: rgba(218, 165, 32, 0.1);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Ajouter un Candidat</h2>
                <a href="candidates.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="candidateForm">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="specialite" class="form-label">Spécialité</label>
                                    <input type="text" class="form-control" id="specialite" name="specialite"
                                           value="<?php echo isset($_POST['specialite']) ? htmlspecialchars($_POST['specialite']) : ''; ?>" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="niveau" class="form-label">Niveau</label>
                                    <select class="form-select" id="niveau" name="niveau" required>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] == $i) ? 'selected' : ''; ?>>
                                                Niveau <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="categorie" class="form-label">Catégorie</label>
                                    <select class="form-select" id="categorie" name="categorie" required>
                                        <option value="">Choisir une catégorie</option>
                                        <option value="Miss" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'Miss') ? 'selected' : ''; ?>>Miss</option>
                                        <option value="Master" <?php echo (isset($_POST['categorie']) && $_POST['categorie'] === 'Master') ? 'selected' : ''; ?>>Master</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message de présentation</label>
                                    <textarea class="form-control" id="message" name="message" rows="4"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Photo</label>
                                <label class="custom-file-upload d-block">
                                    <input type="file" name="profil" id="profil" class="d-none" accept="image/*" required>
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                    <br>
                                    Cliquez ou déposez une image ici
                                </label>
                                <img id="preview" class="preview-image">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary" style="background-color: #DAA520; border: none;">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('profil').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Form validation
document.getElementById('candidateForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('profil');
    const file = fileInput.files[0];
    
    if (!file) {
        e.preventDefault();
        alert('Veuillez sélectionner une photo');
        return;
    }
    
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        e.preventDefault();
        alert('La taille du fichier ne doit pas dépasser 5MB');
        return;
    }
});
</script>

</body>
</html>
