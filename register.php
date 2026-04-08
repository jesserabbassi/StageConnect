<?php
/**
 * StageConnect — Register Page
 * Handles new student registration with bcrypt password hashing
 */

// Start session and load helpers
require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'dashboard.php' : 'offres.php');
}

$errors  = [];
$success = '';
$old     = ['name' => '', 'email' => ''];   // Repopulate form on error

// ── Handle POST ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read and trim inputs
    $name            = trim($_POST['name']            ?? '');
    $email           = trim($_POST['email']           ?? '');
    $password        = $_POST['password']              ?? '';
    $confirmPassword = $_POST['confirm_password']      ?? '';

    // Store old values for form repopulation
    $old = ['name' => e($name), 'email' => e($email)];

    // ── Server-side Validation ──────────────────────────────────
    if (empty($name))               $errors[] = 'Le nom complet est obligatoire.';
    elseif (strlen($name) < 3)      $errors[] = 'Le nom doit contenir au moins 3 caractères.';

    if (empty($email))              $errors[] = 'L\'adresse email est obligatoire.';
    elseif (!isValidEmail($email))  $errors[] = 'Adresse email invalide.';

    if (empty($password))           $errors[] = 'Le mot de passe est obligatoire.';
    elseif (strlen($password) < 8)  $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';

    if ($password !== $confirmPassword) $errors[] = 'Les mots de passe ne correspondent pas.';

    // Check if email is already taken
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        }
    }

    // ── Insert user if no errors ────────────────────────────────
    if (empty($errors)) {
        $hashedPassword = hashPassword($password);

        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hashedPassword, 'student']);

        // Auto-login after registration
        $newUserId = $pdo->lastInsertId();
        $_SESSION['user_id']   = $newUserId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'student';

        setFlash('success', "Bienvenue, $name ! Votre compte a été créé avec succès.");
        redirect('offres.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — StageConnect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
</head>
<body>

<!-- Reuse same header -->
<header class="site-header">
    <nav class="navbar container">
        <a href="index.html" class="navbar-brand">
            <div class="brand-icon">🎓</div>
            <span class="brand-text">Stage<span>Connect</span></span>
        </a>
        <ul class="navbar-nav">
            <li><a href="index.html"   class="nav-link">Accueil</a></li>
            <li><a href="offres.php"   class="nav-link">Offres</a></li>
            <li><a href="login.php"    class="nav-link">Connexion</a></li>
            <li><a href="register.php" class="nav-link active">Inscription</a></li>
        </ul>
        <div class="navbar-actions">
            <a href="login.php" class="btn btn-outline btn-sm">Connexion</a>
        </div>
        <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </nav>
</header>

<!-- ══════════════════════════════════════════════════════════════
     REGISTER FORM
══════════════════════════════════════════════════════════════ -->
<main class="auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="brand-icon" style="width:56px;height:56px;font-size:1.8rem;margin:0 auto 12px;">🎓</div>
            <h2 class="auth-title">Créer un compte</h2>
            <p class="auth-subtitle">Rejoignez StageConnect et trouvez votre stage idéal</p>
        </div>

        <!-- ── PHP Errors ─────────────────────────────────────── -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" data-auto-dismiss>
                <span>⚠</span>
                <ul style="margin:0; padding-left:16px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- ── Registration Form ──────────────────────────────── -->
        <form id="register-form" method="POST" action="register.php" novalidate>

            <!-- Full Name -->
            <div class="form-group">
                <label for="name" class="form-label">Nom complet *</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        placeholder="Ex: Hajjej Mouheb"
                        value="<?= $old['name'] ?>"
                        autocomplete="name"
                        required
                    >
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Adresse email *</label>
                <div class="input-wrapper">
                    <span class="input-icon">✉</span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="votre@email.tn"
                        value="<?= $old['email'] ?>"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe *</label>
                <div class="input-wrapper" style="display:flex; gap:8px;">
                    <span class="input-icon">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Minimum 8 caractères"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" data-toggle-password="password"
                            style="background:none;border:none;cursor:pointer;font-size:1.2rem;padding:0 8px;">👁</button>
                </div>
                <small id="password-strength" style="font-size:.8rem; margin-top:4px; display:block;"></small>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Répétez votre mot de passe"
                        autocomplete="new-password"
                        required
                    >
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px;">
                Créer mon compte →
            </button>

        </form>

        <!-- Login link -->
        <p class="text-center mt-3" style="font-size:.9rem; color:var(--gray-600);">
            Vous avez déjà un compte ?
            <a href="login.php" style="font-weight:700;">Se connecter</a>
        </p>

    </div>
</main>

<script src="js/validation.js"></script>
<script src="js/main.js"></script>
</body>
</html>
