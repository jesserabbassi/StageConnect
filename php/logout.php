<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
}

session_destroy();

header('Location: ../html/accueil.html?status=success&message=' . urlencode('Vous êtes maintenant déconnecté.'));
exit;
