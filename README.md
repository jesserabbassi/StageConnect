# StageConnect

StageConnect est une application web native en **PHP + MySQL** pour la gestion des stages. Elle permet aux étudiants de consulter les offres, créer un portfolio public, téléverser un CV PDF et postuler. Les administrateurs peuvent publier des offres, supprimer des offres et consulter les candidatures reçues.

## Fonctionnalités principales

- Consultation dynamique des offres de stage depuis MySQL
- Inscription et connexion avec `password_hash()` / `password_verify()`
- Sessions PHP avec gestion des rôles `student` et `admin`
- Portfolio étudiant public avec CV PDF
- Candidatures enregistrées et suivies côté étudiant
- Dashboard administrateur avec offres, candidatures et accès aux CV

## Structure du projet

```text
StageConnect/
├── css/
├── html/
├── js/
├── php/
├── sql/
├── uploads/
│   └── cv/
├── index.php
└── README.md
```

## Lancer le projet avec XAMPP

1. Placez le dossier `StageConnect` dans `C:\xampp\htdocs\`.
2. Démarrez **Apache** et **MySQL** depuis le panneau XAMPP.
3. Ouvrez **phpMyAdmin** puis importez le fichier [`sql/database.sql`](./sql/database.sql).
4. Vérifiez et adaptez les identifiants de connexion dans [`php/config.php`](./php/config.php) :
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
5. Ouvrez le projet dans le navigateur :
   - `http://localhost/StageConnect/`

## Flux recommandé

1. Créer un compte étudiant depuis `register.html`
2. Se connecter via `login.html`
3. Compléter le portfolio et téléverser un CV PDF
4. Postuler aux offres depuis `offres.html`
5. Suivre les candidatures depuis `portfolio.html`
6. Promouvoir un compte en administrateur dans MySQL si besoin :

```sql
UPDATE users SET role = 'admin' WHERE email = 'votre-email@example.com';
```

## Sécurité mise en place

- PDO avec requêtes préparées
- Validation des champs obligatoires
- Validation extension + MIME type pour les CV PDF
- Taille maximale de CV fixée à 2 Mo
- Téléversement unique avec `move_uploaded_file()`
- Dossier `uploads/cv` protégé contre l'exécution de scripts
- Sortie JSON et accès admin protégés côté serveur

## Future feature optionnelle

Une intégration **Groq AI** peut être ajoutée plus tard dans la couche PHP pour aider au résumé des profils ou des offres, mais elle n'est pas implémentée dans cette version afin de garder le projet entièrement natif et simple à déployer.
