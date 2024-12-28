<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISESTMA - Élection Miss & Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .navbar-custom {
            background-color: #DAA520;
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: white;
        }
        .navbar-custom .nav-link:hover {
            color: #f8f9fa;
        }
        .vote-count-badge {
            background-color: white;
            color: #DAA520;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">ISESTMA</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="candidates.php">Candidats</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">Résultats</a>
                    </li>
                </ul>
                <div class="navbar-text text-white">
                    <i class="fas fa-poll me-2"></i>
                    Total votes: 
                    <?php
                    $total_votes = $pdo->query("SELECT SUM(votes) FROM candidats")->fetchColumn();
                    echo '<span class="vote-count-badge">' . number_format($total_votes) . '</span>';
                    ?>
                </div>
            </div>
        </div>
    </nav>
