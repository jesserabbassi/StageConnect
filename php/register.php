<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();

$fullName = normalize_text($_POST['full_name'] ?? '');
$email = strtolower(normalize_text($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
    redirect_with_message('../html/register.html', 'error', 'Tous les champs sont obligatoires.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_message('../html/register.html', 'error', 'Veuillez saisir une adresse email valide.');
}

if (strlen($password) < 8) {
    redirect_with_message('../html/register.html', 'error', 'Le mot de passe doit contenir au moins 8 caractères.');
}

if ($password !== $confirmPassword) {
    redirect_with_message('../html/register.html', 'error', 'La confirmation du mot de passe ne correspond pas.');
}

try {
    $pdo = getPDO();

    $checkStatement = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $checkStatement->execute(['email' => $email]);

    if ($checkStatement->fetch()) {
        redirect_with_message('../html/register.html', 'error', 'Un compte existe déjà avec cette adresse email.');
    }

    $insertStatement = $pdo->prepare(
        'INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, :role)'
    );
    $insertStatement->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'student',
    ]);

    redirect_with_message('../html/login.html', 'success', 'Compte créé avec succès. Vous pouvez maintenant vous connecter.');
} catch (PDOException $exception) {
    redirect_with_message('../html/register.html', 'error', 'Impossible de créer le compte pour le moment.');
}
