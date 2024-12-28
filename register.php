<?php
require_once 'includes/config.php';

if(isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'voter')");
            if($stmt->execute([$username, $email, $hashed_password])) {
                header("Location: login.php?success=1");
                exit();
            }
        } else {
            $error = "Cette adresse email est déjà utilisée.";
        }
    } else {
        $error = "Les mots de passe ne correspondent pas.";
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="text-center mb-4" style="color: #DAA520;">Inscription Votant</h2>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" name="register" class="btn w-100" style="background-color: #DAA520; color: white;">
                            S'inscrire
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Déjà inscrit ? <a href="login.php" style="color: #DAA520;">Connectez-vous</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
