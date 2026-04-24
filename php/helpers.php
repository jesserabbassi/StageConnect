<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function append_query(string $path, array $params): string
{
    $separator = str_contains($path, '?') ? '&' : '?';
    return $path . $separator . http_build_query($params);
}

function redirect_with_message(string $path, string $status, string $message): void
{
    redirect(append_query($path, [
        'status' => $status,
        'message' => $message,
    ]));
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_authenticated(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return is_authenticated() && (current_user()['role'] ?? '') === 'admin';
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('../html/accueil.html');
    }
}

function require_login(string $redirectPath): void
{
    if (!is_authenticated()) {
        redirect_with_message($redirectPath, 'error', 'Veuillez vous connecter pour continuer.');
    }
}

function require_admin_redirect(string $redirectPath): void
{
    if (!is_admin()) {
        redirect_with_message($redirectPath, 'error', 'Accès administrateur requis.');
    }
}

function require_admin_json(): void
{
    if (!is_admin()) {
        json_response([
            'message' => 'Accès administrateur requis.',
        ], 403);
    }
}

function normalize_text(?string $value): string
{
    return trim((string) $value);
}

function ensure_upload_dir(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

function sanitize_filename_base(string $filename): string
{
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $base);
    $base = trim((string) $base, '-');
    return $base !== '' ? $base : 'cv';
}

function upload_cv(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Le téléversement du CV a échoué.');
    }

    if (($file['size'] ?? 0) > MAX_CV_SIZE) {
        throw new RuntimeException('Le CV dépasse la taille maximale autorisée de 2 Mo.');
    }

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        throw new RuntimeException('Le CV doit être un fichier PDF.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Fichier de CV invalide.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $tmpName) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedMimeTypes = ['application/pdf', 'application/x-pdf'];
    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        throw new RuntimeException('Le type MIME du fichier n\'est pas autorisé.');
    }

    ensure_upload_dir();

    $safeBase = sanitize_filename_base((string) ($file['name'] ?? 'cv.pdf'));
    $newName = uniqid('cv_', true) . '_' . $safeBase . '.pdf';
    $destination = UPLOAD_DIR . $newName;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Impossible d\'enregistrer le CV sur le serveur.');
    }

    return UPLOAD_RELATIVE_DIR . $newName;
}

function frontend_asset_path(?string $relativePath): ?string
{
    if (!$relativePath) {
        return null;
    }

    return '../' . ltrim($relativePath, '/');
}

function build_public_portfolio_url(int $userId): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/php'));
    $htmlDir = preg_replace('#/php$#', '/html', $scriptDir) ?: '/html';

    return $scheme . '://' . $host . rtrim($htmlDir, '/') . '/portfolio.html?user=' . $userId;
}

function refresh_session_user(PDO $pdo, int $userId): void
{
    $statement = $pdo->prepare('SELECT id, full_name, email, role FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch();

    if ($user) {
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }
}
