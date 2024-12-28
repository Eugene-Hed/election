<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Récupération des candidats Miss
$stmt = $pdo->prepare("SELECT * FROM candidats WHERE categorie = 'Miss' ORDER BY votes DESC LIMIT 3");
$stmt->execute();
$miss_candidates = $stmt->fetchAll();

// Récupération des candidats Master
$stmt = $pdo->prepare("SELECT * FROM candidats WHERE categorie = 'Master' ORDER BY votes DESC LIMIT 3");
$stmt->execute();
$master_candidates = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Élection Miss & Master ISESTMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #DAA520;
        }
        
        .navbar {
            background-color: var(--primary-color);
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('assets/images/hero-bg.jpg');
            background-size: cover;
            height: 80vh;
            color: white;
        }
        
        .btn-custom {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-custom:hover {
            background-color: #B8860B;
            color: white;
        }
        
        .candidate-card {
            border: 1px solid var(--primary-color);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .candidate-card:hover {
            transform: translateY(-5px);
        }
        
        .candidate-image {
            height: 200px;
            object-fit: cover;
        }
        
        .vote-count {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .admin-link {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(100, 97, 97, 0.3);
            text-decoration: none;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .admin-link:hover {
            color: rgba(82, 77, 77, 0.2);
        }
    </style>
</head>
<body>
    <!-- Section Hero -->
    <div class="hero-section d-flex align-items-center">
        <div class="container text-center">
            <h1 class="display-3 mb-4">Élection Miss & Master ISESTMA 2024</h1>
            <p class="lead mb-4">Votez pour vos candidats préférés et participez à cet événement prestigieux</p>
            <a href="candidates.php" class="btn btn-custom btn-lg">Voter maintenant</a>
        </div>
    </div>

    <!-- Section À propos -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4" style="color: var(--primary-color);">À propos de l'événement</h2>
            <div class="row">
                <div class="col-md-6">
                    <p>L'Institut Supérieur d'Études Scientifiques, Technologique et Managériale (ISESTMA) organise son élection annuelle de Miss & Master. Cet événement prestigieux met en valeur l'excellence et le charisme de nos étudiants.</p>
                </div>
                <div class="col-md-6">
                    <p>Les votes sont ouverts à tous. Chaque vote compte et contribue à élire les représentants qui incarneront les valeurs de notre institution.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Comment voter -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4" style="color: var(--primary-color);">Comment voter ?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-circle fa-3x mb-3" style="color: var(--primary-color);"></i>
                            <h4 class="card-title">1. Choisissez</h4>
                            <p class="card-text">Sélectionnez votre candidat(e) préféré(e)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-mobile-alt fa-3x mb-3" style="color: var(--primary-color);"></i>
                            <h4 class="card-title">2. Paiement</h4>
                            <p class="card-text">Effectuez votre paiement via Mobile Money</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x mb-3" style="color: var(--primary-color);"></i>
                            <h4 class="card-title">3. Confirmation</h4>
                            <p class="card-text">Recevez votre confirmation de vote</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Top Candidats -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4" style="color: var(--primary-color);">Top Candidats</h2>
            
            <!-- Miss -->
            <h3 class="mb-4">Miss ISESTMA</h3>
            <div class="row">
                <?php foreach($miss_candidates as $candidate): ?>
                    <div class="col-md-4">
                        <div class="card candidate-card">
                            <span class="vote-count"><?php echo $candidate['votes']; ?> votes</span>
                            <img src="<?php echo escape($candidate['profil']); ?>" 
                                 class="card-img-top candidate-image" 
                                 alt="<?php echo escape($candidate['nom']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo escape($candidate['nom']); ?></h5>
                                <p class="card-text"><?php echo escape($candidate['specialite']); ?></p>
                                <a href="vote.php?id=<?php echo $candidate['id']; ?>" 
                                   class="btn btn-custom">Voter</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Master -->
            <h3 class="mb-4 mt-5">Master ISESTMA</h3>
            <div class="row">
                <?php foreach($master_candidates as $candidate): ?>
                    <div class="col-md-4">
                        <div class="card candidate-card">
                            <span class="vote-count"><?php echo $candidate['votes']; ?> votes</span>
                            <img src="<?php echo escape($candidate['profil']); ?>" 
                                 class="card-img-top candidate-image" 
                                 alt="<?php echo escape($candidate['nom']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo escape($candidate['nom']); ?></h5>
                                <p class="card-text"><?php echo escape($candidate['specialite']); ?></p>
                                <a href="vote.php?id=<?php echo $candidate['id']; ?>" 
                                   class="btn btn-custom">Voter</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="candidates.php" class="btn btn-lg btn-custom">Voir tous les candidats</a>
            </div>
        </div>
    </section>

    <!-- Admin Link -->
    <a href="admin/login.php" class="admin-link">
        <i class="fas fa-lock"></i>
    </a>

    <?php require_once 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
