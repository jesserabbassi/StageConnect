<?php
/**
 * StageConnect — Login Page
 * Authenticates users and creates session variables
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'dashboard.php' : 'offres.php');
}

$error = '';
$oldEmail = '';

// ── Handle POST ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']       ?? '';
    $oldEmail = e($email);

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Look up user by email (using prepared statement — safe from SQL injection)
        $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password'])) {
            // ── Login success: set session variables ────────────
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            setFlash('success', "Bienvenue, {$user['name']} !");

            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('dashboard.php');
            } else {
                redirect('offres.php');
            }

        } else {
            // Generic error message (don't reveal if email exists)
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

// Display session-expired message if redirected from a protected page
$sessionMsg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'session_expired') {
    $sessionMsg = 'Votre session a expiré. Veuillez vous reconnecter.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — StageConnect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
</head>
<body>

<!-- Header -->
<header class="site-header">
    <nav class="navbar container">
        <a href="index.html" class="navbar-brand">
            <div class="brand-icon">🎓</div>
            <span class="brand-text">Stage<span>Connect</span></span>
        </a>
        <ul class="navbar-nav">
            <li><a href="index.html"   class="nav-link">Accueil</a></li>
            <li><a href="offres.php"   class="nav-link">Offres</a></li>
            <li><a href="login.php"    class="nav-link active">Connexion</a></li>
            <li><a href="register.php" class="nav-link">Inscription</a></li>
        </ul>
        <div class="navbar-actions">
            <a href="register.php" class="btn btn-primary btn-sm">S'inscrire</a>
        </div>
        <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </nav>
</header>

<!-- ══════════════════════════════════════════════════════════════
     LOGIN FORM
══════════════════════════════════════════════════════════════ -->
<main class="auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="brand-icon" style="width:56px;height:56px;font-size:1.8rem;margin:0 auto 12px;">🎓</div>
            <h2 class="auth-title">Bon retour !</h2>
            <p class="auth-subtitle">Connectez-vous pour accéder à votre espace</p>
        </div>

        <!-- Session expired message -->
        <?php if ($sessionMsg): ?>
            <div class="alert alert-info" data-auto-dismiss>ℹ <?= e($sessionMsg) ?></div>
        <?php endif; ?>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="alert alert-danger" data-auto-dismiss>⚠ <?= e($error) ?></div>
        <?php endif; ?>

        <!-- Flash message (e.g. from logout) -->
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- ── Login Form ──────────────────────────────────────── -->
        <form id="login-form" method="POST" action="login.php" novalidate>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Adresse email</label>
                <div class="input-wrapper">
                    <span class="input-icon">✉</span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="votre@email.tn"
                        value="<?= $oldEmail ?>"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-wrapper" style="display:flex; gap:8px;">
                    <span class="input-icon">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Votre mot de passe"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" data-toggle-password="password"
                            style="background:none;border:none;cursor:pointer;font-size:1.2rem;padding:0 8px;">👁</button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px;">
                Se connecter →
            </button>

        </form>

        <!-- Demo credentials box -->
        <div style="background:var(--primary-light); border-radius:var(--radius-sm);
             padding:14px 16px; margin-top:20px; border-left:3px solid var(--primary);">
            <p style="font-size:.82rem; font-weight:700; color:var(--primary); margin-bottom:6px;">
                🧪 Comptes de démonstration
            </p>
            <p style="font-size:.8rem; color:var(--gray-600); margin:2px 0;">
                <strong>Admin:</strong> admin@stageconnect.tn / password
            </p>
            <p style="font-size:.8rem; color:var(--gray-600); margin:2px 0;">
                <strong>Étudiant:</strong> mouheb@email.tn / Student123
            </p>
        </div>

        <!-- Register link -->
        <p class="text-center mt-3" style="font-size:.9rem; color:var(--gray-600);">
            Pas encore de compte ?
            <a href="register.php" style="font-weight:700;">Créer un compte</a>
        </p>

    </div>
</main>

<script src="js/validation.js"></script>
<script src="js/main.js"></script>
</body>
</html>
