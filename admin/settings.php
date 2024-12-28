<?php
require_once '../includes/config.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Traitement de la mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour des numéros mobile
    if (isset($_POST['update_mobile'])) {
        $stmt = $pdo->prepare("UPDATE settings SET 
            mobile_number1 = ?,
            mobile_number2 = ?
            WHERE id = 1");
        $stmt->execute([
            $_POST['mobile_number1'],
            $_POST['mobile_number2']
        ]);
        $_SESSION['success'] = "Numéros mobiles mis à jour avec succès";
    }

    // Mise à jour du thème
    if (isset($_POST['update_theme'])) {
        $stmt = $pdo->prepare("UPDATE settings SET 
            primary_color = ?,
            secondary_color = ?,
            text_color = ?
            WHERE id = 1");
        $stmt->execute([
            $_POST['primary_color'],
            $_POST['secondary_color'],
            $_POST['text_color']
        ]);
        $_SESSION['success'] = "Thème mis à jour avec succès";
    }

    header('Location: settings.php');
    exit();
}

// Récupération des paramètres actuels
$settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .settings-card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
            border: 2px solid #ddd;
        }
        .theme-preview {
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 p-4">
            <h2 class="mb-4">Paramètres de la plateforme</h2>

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
                <!-- Configuration Mobile Money -->
                <div class="col-md-6">
                    <div class="card settings-card">
                        <div class="card-header">
                            <h5 class="mb-0">Configuration Mobile Money</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Numéro Mobile 1</label>
                                    <input type="text" class="form-control" name="mobile_number1"
                                           value="<?php echo escape($settings['mobile_number1']); ?>"
                                           placeholder="Ex: +225 0123456789">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Numéro Mobile 2</label>
                                    <input type="text" class="form-control" name="mobile_number2"
                                           value="<?php echo escape($settings['mobile_number2']); ?>"
                                           placeholder="Ex: +225 0123456789">
                                </div>
                                <button type="submit" name="update_mobile" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Configuration du thème -->
                <div class="col-md-6">
                    <div class="card settings-card">
                        <div class="card-header">
                            <h5 class="mb-0">Personnalisation du thème</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Couleur principale</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control form-control-color" 
                                               name="primary_color" value="<?php echo $settings['primary_color']; ?>">
                                        <div class="color-preview" style="background-color: <?php echo $settings['primary_color']; ?>"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Couleur secondaire</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control form-control-color" 
                                               name="secondary_color" value="<?php echo $settings['secondary_color']; ?>">
                                        <div class="color-preview" style="background-color: <?php echo $settings['secondary_color']; ?>"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Couleur du texte</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control form-control-color" 
                                               name="text_color" value="<?php echo $settings['text_color']; ?>">
                                        <div class="color-preview" style="background-color: <?php echo $settings['text_color']; ?>"></div>
                                    </div>
                                </div>

                                <!-- Aperçu du thème -->
                                <div class="theme-preview" id="themePreview">
                                    <h6>Aperçu du thème</h6>
                                    <button class="btn" id="previewButton">Bouton d'exemple</button>
                                    <p class="mt-2" id="previewText">Texte d'exemple</p>
                                </div>

                                <button type="submit" name="update_theme" class="btn btn-primary mt-3">
                                    <i class="fas fa-paint-brush me-2"></i>Appliquer le thème
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mise à jour en temps réel de l'aperçu du thème
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', updateThemePreview);
});

function updateThemePreview() {
    const primaryColor = document.querySelector('input[name="primary_color"]').value;
    const secondaryColor = document.querySelector('input[name="secondary_color"]').value;
    const textColor = document.querySelector('input[name="text_color"]').value;

    const previewButton = document.getElementById('previewButton');
    const previewText = document.getElementById('previewText');

    previewButton.style.backgroundColor = primaryColor;
    previewButton.style.borderColor = primaryColor;
    previewButton.style.color = 'white';

    previewText.style.color = textColor;
    
    document.getElementById('themePreview').style.backgroundColor = secondaryColor;
}

// Initialisation de l'aperçu
updateThemePreview();
</script>

</body>
</html>
