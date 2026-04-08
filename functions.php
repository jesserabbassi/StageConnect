<?php
/**
 * StageConnect - Shared PHP Functions
 * Authentication, validation, session helpers
 */

// ── Session Setup ────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Auth Helpers ─────────────────────────────────────────────────

/**
 * Check if a user is currently logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 */
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a URL and stop execution
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Require the user to be logged in; redirect to login if not
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('../login.php?msg=session_expired');
    }
}

/**
 * Require admin role; redirect to offers if student
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        redirect('../offres.php');
    }
}

// ── Flash Messages ───────────────────────────────────────────────

/**
 * Set a one-time flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Security Helpers ─────────────────────────────────────────────

/**
 * Sanitize output to prevent XSS attacks
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash a password with bcrypt
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify a password against its bcrypt hash
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}
