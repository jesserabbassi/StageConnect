<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();

$email = strtolower(normalize_text($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    redirect_with_message('../html/login.html', 'error', 'Veuillez renseigner votre email et votre mot de passe.');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'SELECT id, full_name, email, password, role FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        redirect_with_message('../html/login.html', 'error', 'Identifiants invalides.');
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    $destination = $user['role'] === 'admin' ? '../html/dashboard.html' : '../html/portfolio.html';
    redirect_with_message($destination, 'success', 'Connexion réussie.');
} catch (PDOException $exception) {
    redirect_with_message('../html/login.html', 'error', 'Impossible de vous connecter pour le moment.');
}
