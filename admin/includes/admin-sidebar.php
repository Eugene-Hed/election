<div class="col-md-2 admin-sidebar p-0">
    <div class="p-4">
        <h4 class="text-center mb-4">
            <i class="fas fa-crown me-2"></i>
            ISESTMA Admin
        </h4>
        <div class="nav flex-column">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?> mb-2">
                <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
            </a>
            <a href="candidates.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'candidates.php' ? 'active' : ''; ?> mb-2">
                <i class="fas fa-users me-2"></i> Candidats
            </a>
            <a href="anciens-gagnants.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'anciens-gagnants.php' ? 'active' : ''; ?> mb-2">
                <i class="fas fa-trophy me-2"></i> Anciens Gagnants
            </a>
            <a href="payments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?> mb-2">
                <i class="fas fa-money-bill me-2"></i> Paiements
                <?php 
                $pending_count = $pdo->query("SELECT COUNT(*) FROM paiement WHERE status = 'en attente'")->fetchColumn();
                if ($pending_count > 0): 
                ?>
                    <span class="badge bg-warning float-end"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="statistics.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'statistics.php' ? 'active' : ''; ?> mb-2">
                <i class="fas fa-chart-bar me-2"></i> Statistiques
            </a>
            <a href="../logout.php" class="nav-link text-danger mt-5">
                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
            </a>
        </div>
    </div>
</div>

<style>
:root {
    --primary-gold: #DAA520;
    --secondary-gold: #FFD700;
    --dark-gold: #B8860B;
}

.admin-sidebar {
    background: linear-gradient(180deg, var(--primary-gold), var(--dark-gold));
    min-height: 100vh;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
}

.nav-link {
    color: white;
    opacity: 0.9;
    transition: all 0.3s ease;
    border-radius: 8px;
    margin-bottom: 5px;
    padding: 10px 15px;
}

.nav-link:hover {
    color: white;
    opacity: 1;
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(5px);
}

.nav-link.active {
    background: white;
    color: var(--primary-gold);
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.nav-link i {
    width: 20px;
    text-align: center;
}

.badge {
    font-size: 0.8em;
    padding: 5px 8px;
}
</style>
