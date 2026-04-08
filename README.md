# 🎓 StageConnect — Plateforme de Gestion des Stages

**Projet académique — Programmation Web et Multimédia**
Étudiants: Hajjej Mouheb | Abbessi Jesser

---

## 📁 Structure du projet

```
stageconnect/
│
├── index.html          ← Page d'accueil (statique)
├── login.php           ← Page de connexion
├── register.php        ← Page d'inscription
├── offres.php          ← Liste des offres de stage
├── postuler.php        ← Formulaire de candidature
├── dashboard.php       ← Tableau de bord admin
├── mon-espace.php      ← Espace personnel étudiant
├── logout.php          ← Déconnexion
│
├── config/
│   └── db.php          ← Connexion PDO à la base de données
│
├── php/
│   └── functions.php   ← Fonctions partagées (auth, flash, sécurité)
│
├── css/
│   └── style.css       ← Styles globaux (Flexbox, responsive, thème bleu/blanc)
│
├── js/
│   ├── main.js         ← Interactions UI (navbar, modal, filter, animations)
│   └── validation.js   ← Validation des formulaires (côté client)
│
├── uploads/            ← CVs uploadés (créé automatiquement)
└── database.sql        ← Schéma SQL + données de démonstration
```

---

## 🚀 Installation

### Prérequis
- PHP 8.0+
- MySQL 5.7+ ou MariaDB
- Serveur local: XAMPP, WAMP, MAMP ou Laragon

### Étapes

**1. Placer le projet dans le dossier serveur**
```
XAMPP  → C:/xampp/htdocs/stageconnect/
WAMP   → C:/wamp/www/stageconnect/
```

**2. Créer la base de données**
- Ouvrir phpMyAdmin → http://localhost/phpmyadmin
- Aller dans "SQL" et coller tout le contenu de `database.sql`
- Cliquer "Exécuter"

**3. Configurer la connexion (si besoin)**
Modifier `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stageconnect');
define('DB_USER', 'root');
define('DB_PASS', '');   // Votre mot de passe MySQL
```

**4. Lancer le projet**
```
http://localhost/stageconnect/
```

---

## 🔐 Comptes de démonstration

| Rôle     | Email                      | Mot de passe |
|----------|----------------------------|--------------|
| Admin    | admin@stageconnect.tn      | password     |
| Étudiant | mouheb@email.tn            | Student123   |
| Étudiant | jesser@email.tn            | Student123   |

> ⚠️ **Pour la production**: changez tous les mots de passe et modifiez les credentials de la base de données.

---

## 🛠️ Technologies utilisées

| Couche     | Technologie         |
|------------|---------------------|
| Front-end  | HTML5, CSS3, JavaScript (Vanilla) |
| Back-end   | PHP 8 (natif, sans framework) |
| Base de données | MySQL avec PDO |
| Sécurité   | bcrypt, PDO prepared statements, session_regenerate_id |

---

## ✅ Fonctionnalités implémentées

### Étudiants
- [x] Inscription avec validation client + serveur
- [x] Connexion sécurisée (bcrypt + session)
- [x] Consultation des offres de stage
- [x] Filtrage dynamique par mot-clé et domaine (JavaScript)
- [x] Candidature en ligne avec upload de CV
- [x] Suivi des candidatures (Mon Espace)
- [x] Déconnexion sécurisée

### Administrateur
- [x] Dashboard avec statistiques
- [x] Ajout d'offres (modal)
- [x] Modification d'offres (modal pré-rempli)
- [x] Suppression d'offres (avec confirmation)
- [x] Consultation de toutes les candidatures
- [x] Changement de statut des candidatures (en attente / acceptée / refusée)
- [x] Gestion des utilisateurs (suppression)

### Sécurité
- [x] Hachage bcrypt des mots de passe
- [x] Requêtes préparées PDO (protection SQL injection)
- [x] Échappement XSS avec htmlspecialchars()
- [x] Session regeneration après connexion
- [x] Vérification des rôles (admin/student) sur chaque page protégée
- [x] Validation des types de fichiers uploadés

---

## 🎨 Design

- **Thème**: Bleu professionnel / Blanc
- **Polices**: Outfit (titres) + Nunito (corps)
- **Mise en page**: Flexbox, CSS Grid, responsive (mobile-first)
- **Animations**: Hover effects, transitions, scroll animations, floating card

---

## 📚 Pages détaillées

### `index.html` — Page d'accueil
- Section héro avec animation et carte flottante
- Barre de statistiques
- Grille de fonctionnalités
- Aperçu des offres récentes
- Section CTA

### `register.php` — Inscription
- Formulaire avec validation temps réel
- Indicateur de force du mot de passe
- Affichage/masquage du mot de passe
- Hachage bcrypt côté serveur

### `login.php` — Connexion
- Authentification sécurisée
- Redirection basée sur le rôle
- Message de session expirée

### `offres.php` — Offres
- Chargement dynamique depuis la BDD
- Filtrage JavaScript en temps réel
- Cartes avec animation au survol
- CTA pour visiteurs non connectés

### `postuler.php` — Candidature
- Layout deux colonnes (formulaire + détails offre)
- Upload de CV avec aperçu
- Prévention des doublons de candidature

### `dashboard.php` — Admin
- Layout sidebar + contenu principal
- 4 sections: stats, offres, candidatures, utilisateurs
- Modales pour ajout/modification
- Formulaires inline pour mise à jour rapide

### `mon-espace.php` — Espace étudiant
- Tableau des candidatures personnelles
- Indicateurs de statut colorés
