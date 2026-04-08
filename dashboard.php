<?php
/**
 * StageConnect — Admin Dashboard
 * Full management panel: users, offers, applications
 * RESTRICTED to admin role only
 */

require_once __DIR__ . '/php/functions.php';
require_once __DIR__ . '/config/db.php';

// Only admins allowed
requireAdmin();

$flash = getFlash();

// ── Handle POST actions ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {

        // ── Add new offer ──────────────────────────────────────
        case 'add_offer':
            $title       = trim($_POST['title']       ?? '');
            $description = trim($_POST['description'] ?? '');
            $company     = trim($_POST['company']     ?? '');
            $location    = trim($_POST['location']    ?? 'Tunis');
            $domain      = trim($_POST['domain']      ?? '');
            $duration    = trim($_POST['duration']    ?? '');

            if ($title && $description && $company && $domain) {
                $stmt = $pdo->prepare(
                    'INSERT INTO offers (title, description, company, location, domain, duration)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$title, $description, $company, $location, $domain, $duration]);
                setFlash('success', "Offre \"$title\" ajoutée avec succès.");
            } else {
                setFlash('danger', 'Tous les champs obligatoires doivent être remplis.');
            }
            redirect('dashboard.php');
            break;

        // ── Edit offer ─────────────────────────────────────────
        case 'edit_offer':
            $id          = (int)($_POST['edit_id'] ?? 0);
            $title       = trim($_POST['edit_title']       ?? '');
            $description = trim($_POST['edit_description'] ?? '');
            $company     = trim($_POST['edit_company']     ?? '');
            $location    = trim($_POST['edit_location']    ?? '');
            $domain      = trim($_POST['edit_domain']      ?? '');
            $duration    = trim($_POST['edit_duration']    ?? '');

            if ($id && $title && $description && $company) {
                $stmt = $pdo->prepare(
                    'UPDATE offers
                     SET title=?, description=?, company=?, location=?, domain=?, duration=?
                     WHERE id=?'
                );
                $stmt->execute([$title, $description, $company, $location, $domain, $duration, $id]);
                setFlash('success', 'Offre mise à jour avec succès.');
            }
            redirect('dashboard.php');
            break;

        // ── Delete offer ───────────────────────────────────────
        case 'delete_offer':
            $id = (int)($_POST['offer_id'] ?? 0);
            if ($id) {
                $stmt = $pdo->prepare('DELETE FROM offers WHERE id = ?');
                $stmt->execute([$id]);
                setFlash('success', 'Offre supprimée avec succès.');
            }
            redirect('dashboard.php');
            break;

        // ── Update application status ──────────────────────────
        case 'update_status':
            $id     = (int)($_POST['app_id']    ?? 0);
            $status = $_POST['new_status'] ?? '';
            $validStatuses = ['en attente', 'acceptée', 'refusée'];
            if ($id && in_array($status, $validStatuses)) {
                $stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
                $stmt->execute([$status, $id]);
                setFlash('success', 'Statut mis à jour.');
            }
            redirect('dashboard.php#applications');
            break;

        // ── Delete user ────────────────────────────────────────
        case 'delete_user':
            $id = (int)($_POST['user_id'] ?? 0);
            // Prevent admin from deleting themselves
            if ($id && $id !== (int)$_SESSION['user_id']) {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
                $stmt->execute([$id]);
                setFlash('success', 'Utilisateur supprimé.');
            }
            redirect('dashboard.php#users');
            break;
    }
}

// ── Load all data for display ──────────────────────────────────
$offers       = $pdo->query('SELECT * FROM offers ORDER BY created_at DESC')->fetchAll();
$applications = $pdo->query(
    'SELECT a.*, u.name AS student_name, u.email AS student_email, o.title AS offer_title
     FROM applications a
     JOIN users u  ON a.user_id  = u.id
     JOIN offers o ON a.offer_id = o.id
     ORDER BY a.applied_at DESC'
)->fetchAll();
$users = $pdo->query(
    'SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC'
)->fetchAll();

// Stats
$totalOffers  = count($offers);
$totalApps    = count($applications);
$totalUsers   = count($users);
$pendingApps  = count(array_filter($applications, fn($a) => $a['status'] === 'en attente'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — StageConnect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
</head>
<body>

<!-- Header (simplified for admin) -->
<header class="site-header">
    <nav class="navbar container">
        <a href="index.html" class="navbar-brand">
            <div class="brand-icon">🎓</div>
            <span class="brand-text">Stage<span>Connect</span></span>
        </a>
        <ul class="navbar-nav">
            <li><a href="offres.php"    class="nav-link">Voir les offres</a></li>
            <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
        </ul>
        <div class="navbar-actions">
            <span style="font-size:.88rem; color:var(--gray-600); font-weight:600;">
                🛡 <?= e($_SESSION['user_name']) ?>
            </span>
            <a href="logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
        </div>
        <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </nav>
</header>

<!-- ══════════════════════════════════════════════════════════════
     DASHBOARD LAYOUT
══════════════════════════════════════════════════════════════ -->
<div class="dashboard-layout">

    <!-- ── Sidebar ──────────────────────────────────────────── -->
    <aside class="sidebar">
        <div class="sidebar-label">Menu Admin</div>

        <div class="sidebar-section">
            <a href="#overview"      class="sidebar-link active">
                <span class="icon">📊</span> Vue d'ensemble
            </a>
            <a href="#offers"        class="sidebar-link">
                <span class="icon">📋</span> Gestion des offres
            </a>
            <a href="#applications"  class="sidebar-link">
                <span class="icon">📄</span> Candidatures
                <?php if ($pendingApps > 0): ?>
                    <span class="badge badge-warning" style="margin-left:auto;"><?= $pendingApps ?></span>
                <?php endif; ?>
            </a>
            <a href="#users"         class="sidebar-link">
                <span class="icon">👥</span> Utilisateurs
            </a>
        </div>

        <div class="sidebar-label">Actions</div>
        <div class="sidebar-section">
            <a href="#" onclick="openModal('add-offer-modal'); return false;" class="sidebar-link">
                <span class="icon">➕</span> Ajouter une offre
            </a>
            <a href="offres.php" class="sidebar-link">
                <span class="icon">🔗</span> Voir le site
            </a>
            <a href="logout.php" class="sidebar-link">
                <span class="icon">🚪</span> Déconnexion
            </a>
        </div>
    </aside>

    <!-- ── Main Dashboard Content ───────────────────────────── -->
    <main class="dashboard-content">

        <!-- Flash message -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- ═══════════ SECTION: Overview ═══════════ -->
        <section id="overview">
            <div class="dash-header">
                <div>
                    <h2>Tableau de bord</h2>
                    <p>Bienvenue, <?= e($_SESSION['user_name']) ?> — Administrateur</p>
                </div>
                <button onclick="openModal('add-offer-modal')" class="btn btn-primary">
                    ➕ Nouvelle offre
                </button>
            </div>

            <!-- Stats cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-number"><?= $totalOffers ?></span>
                    <div class="stat-label">Offres publiées</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $totalApps ?></span>
                    <div class="stat-label">Candidatures</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $pendingApps ?></span>
                    <div class="stat-label">En attente</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $totalUsers ?></span>
                    <div class="stat-label">Utilisateurs</div>
                </div>
            </div>
        </section>

        <!-- ═══════════ SECTION: Offers ═══════════ -->
        <section id="offers" style="margin-top:48px;">
            <div class="dash-header">
                <h3>Offres de stage (<?= $totalOffers ?>)</h3>
                <button onclick="openModal('add-offer-modal')" class="btn btn-primary btn-sm">
                    ➕ Ajouter
                </button>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Entreprise</th>
                            <th>Domaine</th>
                            <th>Durée</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($offers)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--gray-400); padding:24px;">
                                    Aucune offre publiée.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($offers as $offer): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:.85rem;"><?= $offer['id'] ?></td>
                                <td><strong><?= e($offer['title']) ?></strong></td>
                                <td><?= e($offer['company']) ?></td>
                                <td><span class="badge badge-primary"><?= e($offer['domain']) ?></span></td>
                                <td><?= e($offer['duration']) ?></td>
                                <td style="font-size:.82rem; color:var(--gray-400);">
                                    <?= date('d/m/Y', strtotime($offer['created_at'])) ?>
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                        <!-- Edit button — passes data via data-* attributes -->
                                        <button class="btn btn-outline btn-sm"
                                            data-edit-offer
                                            data-edit_id="<?= $offer['id'] ?>"
                                            data-edit_title="<?= e($offer['title']) ?>"
                                            data-edit_description="<?= e($offer['description']) ?>"
                                            data-edit_company="<?= e($offer['company']) ?>"
                                            data-edit_location="<?= e($offer['location']) ?>"
                                            data-edit_domain="<?= e($offer['domain']) ?>"
                                            data-edit_duration="<?= e($offer['duration']) ?>">
                                            ✏️ Modifier
                                        </button>
                                        <!-- Delete -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action"   value="delete_offer">
                                            <input type="hidden" name="offer_id" value="<?= $offer['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    data-confirm="Supprimer cette offre ?">
                                                🗑 Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ═══════════ SECTION: Applications ═══════════ -->
        <section id="applications" style="margin-top:48px;">
            <h3 style="margin-bottom:20px;">Candidatures (<?= $totalApps ?>)</h3>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Étudiant</th>
                            <th>Offre</th>
                            <th>CV</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Changer statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:var(--gray-400); padding:24px;">
                                    Aucune candidature reçue.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:.85rem;"><?= $app['id'] ?></td>
                                <td>
                                    <div style="font-weight:600;"><?= e($app['student_name']) ?></div>
                                    <div style="font-size:.78rem; color:var(--gray-400);"><?= e($app['student_email']) ?></div>
                                </td>
                                <td style="font-size:.88rem;"><?= e($app['offer_title']) ?></td>
                                <td>
                                    <?php if ($app['cv']): ?>
                                        <a href="uploads/<?= e($app['cv']) ?>"
                                           target="_blank" class="btn btn-outline btn-sm">
                                            📄 Voir CV
                                        </a>
                                    <?php else: ?>
                                        <span style="color:var(--gray-400); font-size:.8rem;">—</span>
                                    <?php endif; ?>
                                </td>
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
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= e($app['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Quick status update form -->
                                    <form method="POST" style="display:flex; gap:6px; align-items:center;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                        <select name="new_status" class="form-control"
                                                style="padding:6px 10px; font-size:.82rem;">
                                            <option value="en attente" <?= $app['status']==='en attente' ? 'selected':'' ?>>⏳ En attente</option>
                                            <option value="acceptée"   <?= $app['status']==='acceptée'   ? 'selected':'' ?>>✅ Acceptée</option>
                                            <option value="refusée"    <?= $app['status']==='refusée'    ? 'selected':'' ?>>❌ Refusée</option>
                                        </select>
                                        <button type="submit" class="btn btn-success btn-sm">✓</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ═══════════ SECTION: Users ═══════════ -->
        <section id="users" style="margin-top:48px; margin-bottom:40px;">
            <h3 style="margin-bottom:20px;">Utilisateurs (<?= $totalUsers ?>)</h3>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Inscription</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td style="color:var(--gray-400); font-size:.85rem;"><?= $user['id'] ?></td>
                                <td><strong><?= e($user['name']) ?></strong></td>
                                <td style="font-size:.88rem;"><?= e($user['email']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role']==='admin' ? 'badge-danger' : 'badge-primary' ?>">
                                        <?= e($user['role']) ?>
                                    </span>
                                </td>
                                <td style="font-size:.82rem; color:var(--gray-400);">
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action"  value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    data-confirm="Supprimer cet utilisateur ?">
                                                🗑 Supprimer
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size:.8rem; color:var(--gray-400);">Protégé</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div><!-- end dashboard-layout -->

<!-- ══════════════════════════════════════════════════════════════
     MODAL: Add Offer
══════════════════════════════════════════════════════════════ -->
<div id="add-offer-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Ajouter une offre</h3>
            <button class="modal-close" onclick="closeModal('add-offer-modal')">✕</button>
        </div>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="add_offer">

            <div class="form-group">
                <label class="form-label">Titre du poste *</label>
                <input type="text" name="title" class="form-control"
                       placeholder="Ex: Développeur Web Front-End" required>
            </div>
            <div class="form-group">
                <label class="form-label">Entreprise *</label>
                <input type="text" name="company" class="form-control"
                       placeholder="Nom de l'entreprise" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Domaine *</label>
                    <input type="text" name="domain" class="form-control"
                           placeholder="Ex: Développement Web" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Durée</label>
                    <input type="text" name="duration" class="form-control"
                           placeholder="Ex: 3 mois">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Localisation</label>
                <input type="text" name="location" class="form-control"
                       placeholder="Ex: Tunis">
            </div>
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-control" rows="4"
                          placeholder="Décrivez le poste..." required></textarea>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" class="btn btn-outline"
                        onclick="closeModal('add-offer-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Publier l'offre</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL: Edit Offer
══════════════════════════════════════════════════════════════ -->
<div id="edit-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>✏️ Modifier l'offre</h3>
            <button class="modal-close" onclick="closeModal('edit-modal')">✕</button>
        </div>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="action" value="edit_offer">
            <input type="hidden" name="edit_id" id="edit_id">

            <div class="form-group">
                <label class="form-label">Titre *</label>
                <input type="text" name="edit_title" id="edit_title"
                       class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Entreprise *</label>
                <input type="text" name="edit_company" id="edit_company"
                       class="form-control" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Domaine</label>
                    <input type="text" name="edit_domain" id="edit_domain"
                           class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Durée</label>
                    <input type="text" name="edit_duration" id="edit_duration"
                           class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Localisation</label>
                <input type="text" name="edit_location" id="edit_location"
                       class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="edit_description" id="edit_description"
                          class="form-control" rows="4" required></textarea>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" class="btn btn-outline"
                        onclick="closeModal('edit-modal')">Annuler</button>
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
            </div>
        </form>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>
