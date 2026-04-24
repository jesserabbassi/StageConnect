<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    ensureDatabaseSchema($pdo);

    return $pdo;
}

function ensureDatabaseSchema(PDO $pdo): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    createMissingTables($pdo);
    migrateUsersTable($pdo);
    migrateOffersTable($pdo);
    migrateApplicationsTable($pdo);
    migratePortfoliosTable($pdo);

    $checked = true;
}

function createMissingTables(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS offers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            company VARCHAR(150) NOT NULL,
            location VARCHAR(150) NOT NULL,
            duration VARCHAR(80) NOT NULL,
            description TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            offer_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_applications_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_applications_offer
                FOREIGN KEY (offer_id) REFERENCES offers(id)
                ON DELETE CASCADE,
            UNIQUE KEY unique_application (user_id, offer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS portfolios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            skills TEXT DEFAULT NULL,
            education TEXT DEFAULT NULL,
            experience TEXT DEFAULT NULL,
            languages TEXT DEFAULT NULL,
            cv_path VARCHAR(255) DEFAULT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_portfolios_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,
            UNIQUE KEY unique_portfolio_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function migrateUsersTable(PDO $pdo): void
{
    if (columnExists($pdo, 'users', 'name') && !columnExists($pdo, 'users', 'full_name')) {
        $pdo->exec("ALTER TABLE users CHANGE COLUMN name full_name VARCHAR(120) NOT NULL");
    }

    if (!columnExists($pdo, 'users', 'full_name')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(120) NOT NULL DEFAULT '' AFTER id");
    }
}

function migrateOffersTable(PDO $pdo): void
{
    if (!columnExists($pdo, 'offers', 'company')) {
        $pdo->exec("ALTER TABLE offers ADD COLUMN company VARCHAR(150) NOT NULL DEFAULT 'Entreprise' AFTER title");
    }

    if (!columnExists($pdo, 'offers', 'location')) {
        $pdo->exec("ALTER TABLE offers ADD COLUMN location VARCHAR(150) NOT NULL DEFAULT 'Tunisie' AFTER company");
    }

    if (!columnExists($pdo, 'offers', 'duration')) {
        $pdo->exec("ALTER TABLE offers ADD COLUMN duration VARCHAR(80) NOT NULL DEFAULT '2 mois' AFTER location");
    }

    if (!columnExists($pdo, 'offers', 'created_at')) {
        $pdo->exec("ALTER TABLE offers ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }
}

function migrateApplicationsTable(PDO $pdo): void
{
    if (columnExists($pdo, 'applications', 'applied_at') && !columnExists($pdo, 'applications', 'created_at')) {
        $pdo->exec("ALTER TABLE applications CHANGE COLUMN applied_at created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    if (!columnExists($pdo, 'applications', 'created_at')) {
        $pdo->exec("ALTER TABLE applications ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    if (!columnExists($pdo, 'applications', 'status')) {
        $pdo->exec("ALTER TABLE applications ADD COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending'");
    } else {
        $pdo->exec("ALTER TABLE applications MODIFY status VARCHAR(32) NOT NULL DEFAULT 'pending'");
    }

    $pdo->exec(
        "UPDATE applications
         SET status = CASE
             WHEN LOWER(status) IN ('pending', 'accepted', 'rejected') THEN LOWER(status)
             WHEN LOWER(status) LIKE 'en attente%' THEN 'pending'
             WHEN LOWER(status) LIKE 'accept%' THEN 'accepted'
             WHEN LOWER(status) LIKE 'refus%' THEN 'rejected'
             ELSE 'pending'
         END"
    );

    $pdo->exec("ALTER TABLE applications MODIFY status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
}

function migratePortfoliosTable(PDO $pdo): void
{
    if (!columnExists($pdo, 'portfolios', 'phone')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN phone VARCHAR(50) DEFAULT NULL AFTER user_id");
    }

    if (!columnExists($pdo, 'portfolios', 'bio')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN bio TEXT DEFAULT NULL AFTER phone");
    }

    if (!columnExists($pdo, 'portfolios', 'skills')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN skills TEXT DEFAULT NULL AFTER bio");
    }

    if (!columnExists($pdo, 'portfolios', 'education')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN education TEXT DEFAULT NULL AFTER skills");
    }

    if (!columnExists($pdo, 'portfolios', 'experience')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN experience TEXT DEFAULT NULL AFTER education");
    }

    if (!columnExists($pdo, 'portfolios', 'languages')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN languages TEXT DEFAULT NULL AFTER experience");
    }

    if (!columnExists($pdo, 'portfolios', 'cv_path')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN cv_path VARCHAR(255) DEFAULT NULL AFTER languages");
    }

    if (!columnExists($pdo, 'portfolios', 'updated_at')) {
        $pdo->exec("ALTER TABLE portfolios ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :schema
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column"
    );
    $statement->execute([
        'schema' => DB_NAME,
        'table' => $table,
        'column' => $column,
    ]);

    return (int) $statement->fetchColumn() > 0;
}
