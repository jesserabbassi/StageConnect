<?php
/**
 * StageConnect - Database Configuration
 * Uses PDO with prepared statements for security
 * 
 * HOW TO USE:
 *   require_once __DIR__ . '/../config/db.php';
 *   // $pdo is now available
 */

// ── Database credentials ────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'stageconnect');
define('DB_USER', 'root');       // ⚠️ Change in production
define('DB_PASS', '');           // ⚠️ Change in production
define('DB_CHAR', 'utf8mb4');

// ── PDO Connection ──────────────────────────────────────────────
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Return associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                     // Use real prepared statements
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // In production, never expose the error message to users
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['error' => 'Connexion à la base de données impossible.']));
}
