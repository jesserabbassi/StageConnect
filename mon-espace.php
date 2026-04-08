<?php
/**
 * StageConnect — Mon Espace Étudiant
 * Students can view all their submitted applications and their statuses
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/config/db.php';

requireLogin();
if (isAdmin()) redirect('dashboard.php');

$flash = getFlash();

// Fetch student's applications with offer details
$stmt = $pdo->prepare(
    'SELECT a.id, a.cv, a.status, a.applied_at,
            o.title AS offer_title, o.company, o.domain, o.location
     FROM applications a
     JOIN offers o ON a.offer_id = o.id
     WHERE a.user_id = ?
     ORDER BY a.applied_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace — StageConnect</title>
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
            <li><a href="index.html"     class="nav-link">Accueil</a></li>
            <li><a href="offres.php"     class="nav-link">Offres</a></li>
            <li><a href="mon-espace.php" class="nav-link active">Mon espace</a></li>
            <li><a href="logout.php"     class="nav-link">Déconnexion</a></li>
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

<!-- Page hero -->
<section class="page-hero">
    <div class="container" style="position:relative;z-index:1;">
        <h1>Mon espace étudiant</h1>
        <p>Suivez l'état de toutes vos candidatures</p>
    </div>
</section>

<!-- Main content -->
<main class="container" style="padding: 40px 0 60px;">

    <!-- Flash -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="card" style="margin-bottom:32px; display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="width:60px; height:60px; background:linear-gradient(135deg, var(--primary), var(--accent));
             border-radius:50%; display:flex; align-items:center; justify-content:center;
             font-size:1.6rem; color:white; flex-shrink:0;">
            🎓
        </div>
        <div>
            <h3 style="margin-bottom:2px;"><?= e($_SESSION['user_name']) ?></h3>
            <p style="font-size:.88rem; margin:0;">Étudiant — <?= count($applications) ?> candidature(s) envoyée(s)</p>
        </div>
        <a href="offres.php" class="btn btn-primary btn-sm" style="margin-left:auto;">
            + Postuler à une nouvelle offre
        </a>
    </div>

    <h3 style="margin-bottom:20px;">Mes candidatures</h3>

    <?php if (empty($applications)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>Aucune candidature</h3>
            <p>Vous n'avez pas encore postulé. Découvrez nos offres disponibles.</p>
            <a href="offres.php" class="btn btn-primary mt-2">Voir les offres</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Poste</th>
                        <th>Entreprise</th>
                        <th>Domaine</th>
                        <th>Localisation</th>
                        <th>Date de candidature</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><strong><?= e($app['offer_title']) ?></strong></td>
                            <td><?= e($app['company']) ?></td>
                            <td><span class="badge badge-primary"><?= e($app['domain']) ?></span></td>
                            <td>📍 <?= e($app['location']) ?></td>
                            <td style="font-size:.82rem; color:var(--gray-400);">
                                <?= date('d/m/Y', strtotime($app['applied_at'])) ?>
                            </td>
                            <td>
                                <?php
                                $badgeClass = match($app['status']) {
                                    'acceptée' => 'badge-success',
                                    'refusée'  => 'badge-danger',
                                    default    => 'badge-warning',
                                };
                                $icon = match($app['status']) {
                                    'acceptée' => '✅',
                                    'refusée'  => '❌',
                                    default    => '⏳',
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $icon ?> <?= e($app['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-bottom">
            <span>© 2025 StageConnect. Projet académique.</span>
            <a href="offres.php" style="color:var(--accent);">Voir les offres →</a>
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
