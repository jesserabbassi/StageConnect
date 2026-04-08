<?php
/**
 * StageConnect — Offers Page
 * Lists all available internship offers, with filtering support
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/config/db.php';

// ── Fetch all offers from database ─────────────────────────────
$stmt   = $pdo->query('SELECT * FROM offers ORDER BY created_at DESC');
$offers = $stmt->fetchAll();

// ── Flash message (e.g. after successful application) ──────────
$flash = getFlash();

// ── Get unique domains for filter dropdown ──────────────────────
$domainsStmt = $pdo->query('SELECT DISTINCT domain FROM offers ORDER BY domain');
$domains     = $domainsStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres de Stage — StageConnect</title>
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
            <li><a href="offres.php"   class="nav-link active">Offres</a></li>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="mon-espace.php" class="nav-link">Mon espace</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="nav-link">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="login.php"    class="nav-link">Connexion</a></li>
                <li><a href="register.php" class="nav-link">Inscription</a></li>
            <?php endif; ?>
        </ul>
        <div class="navbar-actions">
            <?php if (isLoggedIn()): ?>
                <span style="font-size:.88rem; color:var(--gray-600); font-weight:600;">
                    👋 <?= e($_SESSION['user_name']) ?>
                </span>
                <a href="logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            <?php else: ?>
                <a href="login.php"    class="btn btn-outline btn-sm">Connexion</a>
                <a href="register.php" class="btn btn-primary btn-sm">S'inscrire</a>
            <?php endif; ?>
        </div>
        <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </nav>
</header>

<!-- ══════════════════════════════════════════════════════════════
     PAGE HERO
══════════════════════════════════════════════════════════════ -->
<section class="page-hero">
    <div class="container" style="position:relative; z-index:1;">
        <h1>Offres de Stage</h1>
        <p><?= count($offers) ?> opportunité<?= count($offers) > 1 ? 's' : '' ?> disponible<?= count($offers) > 1 ? 's' : '' ?></p>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════════════════════════════ -->
<main class="container" style="padding-top:40px; padding-bottom:60px;">

    <!-- Flash message -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- ── Filter Bar ──────────────────────────────────────────── -->
    <div class="filter-bar">
        <div class="form-group">
            <label class="form-label">🔍 Rechercher</label>
            <input
                type="text"
                id="search-offers"
                class="form-control"
                placeholder="Titre, entreprise..."
            >
        </div>
        <div class="form-group">
            <label class="form-label">📂 Domaine</label>
            <select id="filter-domain" class="form-control">
                <option value="">Tous les domaines</option>
                <?php foreach ($domains as $domain): ?>
                    <option value="<?= e($domain) ?>"><?= e($domain) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button onclick="document.getElementById('search-offers').value='';
                         document.getElementById('filter-domain').value='';
                         document.getElementById('search-offers').dispatchEvent(new Event('input'));"
                class="btn btn-outline" style="margin-top:22px;">
            ✕ Réinitialiser
        </button>
    </div>

    <!-- ── Offers Grid ─────────────────────────────────────────── -->
    <div class="offers-grid" id="offers-grid">

        <?php if (empty($offers)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>Aucune offre disponible</h3>
                <p>Revenez bientôt, de nouvelles offres seront publiées prochainement.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($offers as $offer): ?>
            <!-- data-* attributes are used by the JavaScript filter -->
            <div class="offer-card-wrapper"
                 data-title="<?= e($offer['title']) ?>"
                 data-company="<?= e($offer['company']) ?>"
                 data-domain="<?= e($offer['domain']) ?>">

                <article class="offer-card">

                    <!-- Company header -->
                    <div class="offer-company">
                        <div class="company-avatar">
                            <?= e(strtoupper(substr($offer['company'], 0, 2))) ?>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:.9rem; color:var(--navy);">
                                <?= e($offer['company']) ?>
                            </div>
                            <div style="font-size:.78rem; color:var(--gray-400);">
                                📍 <?= e($offer['location']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="offer-title"><?= e($offer['title']) ?></h3>

                    <!-- Badges -->
                    <div class="offer-meta">
                        <span class="badge badge-primary"><?= e($offer['domain']) ?></span>
                        <span class="badge badge-gray">⏱ <?= e($offer['duration']) ?></span>
                    </div>

                    <!-- Description (truncated) -->
                    <p class="offer-desc">
                        <?= e(substr($offer['description'], 0, 140)) ?>...
                    </p>

                    <!-- Card footer: date + action -->
                    <div class="offer-footer">
                        <span style="font-size:.78rem; color:var(--gray-400);">
                            📅 <?= date('d/m/Y', strtotime($offer['created_at'])) ?>
                        </span>

                        <?php if (isLoggedIn() && !isAdmin()): ?>
                            <a href="postuler.php?id=<?= (int)$offer['id'] ?>"
                               class="btn btn-primary btn-sm">Postuler</a>
                        <?php elseif (isAdmin()): ?>
                            <span class="badge badge-gray">Admin</span>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline btn-sm">
                                Connexion requise
                            </a>
                        <?php endif; ?>
                    </div>

                </article>
            </div>
        <?php endforeach; ?>

        <!-- Empty state shown by JavaScript when all filtered out -->
        <div id="empty-state" class="empty-state" style="display:none;">
            <div class="empty-icon">🔍</div>
            <h3>Aucun résultat trouvé</h3>
            <p>Essayez de modifier vos critères de recherche.</p>
        </div>

    </div>

    <!-- Unauthenticated CTA -->
    <?php if (!isLoggedIn()): ?>
        <div style="background:var(--primary-light); border-radius:var(--radius);
             padding:28px; text-align:center; margin-top:16px;
             border: 2px dashed var(--primary);">
            <h3 style="margin-bottom:8px;">Intéressé par ces offres ?</h3>
            <p style="margin-bottom:16px; color:var(--gray-600);">
                Créez un compte gratuit pour postuler en quelques secondes.
            </p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="register.php" class="btn btn-primary">Créer un compte</a>
                <a href="login.php"    class="btn btn-outline">Se connecter</a>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-bottom">
            <span>© 2025 StageConnect. Projet académique.</span>
            <a href="index.html" style="color:var(--accent);">← Retour à l'accueil</a>
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
