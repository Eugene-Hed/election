<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'election2');
define('DB_USER', 'hedric');
define('DB_PASS', 'Hedric&2002');
define('DB_CHARSET', 'utf8mb4');

try {
    // Configuration DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // Options PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Création de l'instance PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch(PDOException $e) {
    // En cas d'erreur, afficher le message et arrêter le script
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction pour échapper les caractères spéciaux
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
