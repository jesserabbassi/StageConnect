<?php
/**
 * StageConnect — Application (Postuler) Page
 * Allows authenticated students to apply for an internship offer
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/config/db.php';

// Must be logged in as a student
requireLogin();
if (isAdmin()) {
    redirect('dashboard.php');
}

// Get offer ID from URL
$offerId = (int)($_GET['id'] ?? 0);
if ($offerId <= 0) {
    redirect('offres.php');
}

// Fetch the offer
$stmt  = $pdo->prepare('SELECT * FROM offers WHERE id = ?');
$stmt->execute([$offerId]);
$offer = $stmt->fetch();

if (!$offer) {
    setFlash('danger', 'Offre introuvable.');
    redirect('offres.php');
}

// Check if student already applied
$alreadyStmt = $pdo->prepare(
    'SELECT id FROM applications WHERE user_id = ? AND offer_id = ?'
);
$alreadyStmt->execute([$_SESSION['user_id'], $offerId]);
$alreadyApplied = $alreadyStmt->fetch();

$errors  = [];
$success = '';

// ── Handle POST ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyApplied) {

    $cvFilename = null;

    // Handle CV file upload
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['cv'];
        $maxSize   = 5 * 1024 * 1024; // 5 MB
        $allowedTypes = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if ($file['size'] > $maxSize) {
            $errors[] = 'Le fichier CV ne doit pas dépasser 5 Mo.';
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Seuls les fichiers PDF et Word sont acceptés.';
        } else {
            // Generate a safe, unique filename
            $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
            $cvFilename = 'cv_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $uploadDir  = __DIR__ . '/uploads/';

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $cvFilename)) {
                $errors[] = 'Erreur lors du téléchargement du fichier.';
                $cvFilename = null;
            }
        }
    } else {
        $errors[] = 'Veuillez télécharger votre CV.';
    }

    // Insert application if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO applications (user_id, offer_id, cv, status) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$_SESSION['user_id'], $offerId, $cvFilename, 'en attente']);

            setFlash('success', 'Votre candidature a été envoyée avec succès ! Bonne chance 🎉');
            redirect('offres.php');

        } catch (PDOException $e) {
            // Duplicate application (UNIQUE constraint violation)
            if ($e->getCode() === '23000') {
                $errors[] = 'Vous avez déjà postulé à cette offre.';
            } else {
                $errors[] = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postuler — <?= e($offer['title']) ?></title>
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
            <li><a href="index.html" class="nav-link">Accueil</a></li>
            <li><a href="offres.php" class="nav-link">Offres</a></li>
            <li><a href="logout.php" class="nav-link">Déconnexion</a></li>
        </ul>
        <div class="navbar-actions">
            <span style="font-size:.88rem; color:var(--gray-600); font-weight:600;">
                👋 <?= e($_SESSION['user_name']) ?>
            </span>
            <a href="logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
        </div>
        <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </nav>
</header>

<!-- Breadcrumb -->
<div style="background:var(--white); border-bottom:1px solid var(--gray-200); padding:12px 0;">
    <div class="container" style="font-size:.85rem; color:var(--gray-600);">
        <a href="index.html">Accueil</a> &rsaquo;
        <a href="offres.php">Offres</a> &rsaquo;
        <span style="color:var(--navy); font-weight:600;">Postuler</span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════════════════════════════ -->
<main class="container">
    <div class="apply-layout">

        <!-- ── LEFT: Application Form ─────────────────────────── -->
        <div>
            <div class="card">
                <h2 style="margin-bottom:4px;">Soumettre ma candidature</h2>
                <p style="margin-bottom:28px; color:var(--gray-600);">
                    Vous postulez pour: <strong style="color:var(--primary);">
                        <?= e($offer['title']) ?>
                    </strong>
                </p>

                <!-- Already applied notice -->
                <?php if ($alreadyApplied): ?>
                    <div class="alert alert-info">
                        ✅ Vous avez déjà postulé à cette offre.
                        <a href="offres.php">Voir d'autres offres →</a>
                    </div>
                <?php else: ?>

                    <!-- Errors -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul style="margin:0; padding-left:16px;">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Application form -->
                    <form id="apply-form" method="POST"
                          action="postuler.php?id=<?= (int)$offerId ?>"
                          enctype="multipart/form-data" novalidate>

                        <!-- Applicant info (read-only, from session) -->
                        <div class="form-group">
                            <label class="form-label">👤 Nom complet</label>
                            <input type="text" class="form-control"
                                   value="<?= e($_SESSION['user_name']) ?>" disabled>
                        </div>

                        <!-- CV Upload -->
                        <div class="form-group">
                            <label for="cv" class="form-label">📎 Votre CV *</label>
                            <input
                                type="file"
                                id="cv"
                                name="cv"
                                class="form-control"
                                accept=".pdf,.doc,.docx"
                                required
                            >
                            <small style="color:var(--gray-400); font-size:.8rem; margin-top:4px; display:block;">
                                Formats acceptés: PDF, Word (.doc, .docx) — Max 5 Mo
                            </small>
                            <!-- Preview shown by JS after file selection -->
                            <div id="cv-preview" style="display:none; margin-top:8px;
                                 background:var(--primary-light); border-radius:6px;
                                 padding:8px 12px; font-size:.85rem; color:var(--primary);">
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary btn-lg"
                                style="width:100%; justify-content:center;">
                            Envoyer ma candidature 🚀
                        </button>

                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── RIGHT: Offer Details Sidebar ───────────────────── -->
        <div>
            <div class="card" style="position:sticky; top:90px;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                    <div class="company-avatar" style="width:52px;height:52px;font-size:1.1rem;">
                        <?= e(strtoupper(substr($offer['company'], 0, 2))) ?>
                    </div>
                    <div>
                        <div style="font-weight:700; color:var(--navy);">
                            <?= e($offer['company']) ?>
                        </div>
                        <div style="font-size:.8rem; color:var(--gray-400);">
                            📍 <?= e($offer['location']) ?>
                        </div>
                    </div>
                </div>

                <h3 style="margin-bottom:12px;"><?= e($offer['title']) ?></h3>

                <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:.88rem;">
                        <span>📂</span>
                        <span><?= e($offer['domain']) ?></span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:.88rem;">
                        <span>⏱</span>
                        <span><?= e($offer['duration']) ?></span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:.88rem;">
                        <span>📅</span>
                        <span>Publié le <?= date('d/m/Y', strtotime($offer['created_at'])) ?></span>
                    </div>
                </div>

                <hr style="border:none; border-top:1px solid var(--gray-200); margin:16px 0;">

                <h4 style="margin-bottom:8px; font-size:.9rem;">Description</h4>
                <p style="font-size:.85rem; line-height:1.7; color:var(--gray-600);">
                    <?= nl2br(e($offer['description'])) ?>
                </p>
            </div>
        </div>

    </div>
</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-bottom">
            <span>© 2025 StageConnect. Projet académique.</span>
            <a href="offres.php" style="color:var(--accent);">← Retour aux offres</a>
        </div>
    </div>
</footer>

<script src="js/validation.js"></script>
<script src="js/main.js"></script>
</body>
</html>
