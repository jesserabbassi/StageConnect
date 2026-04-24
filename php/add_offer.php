<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();
require_admin_redirect('../html/dashboard.html');

$title = normalize_text($_POST['title'] ?? '');
$company = normalize_text($_POST['company'] ?? '');
$location = normalize_text($_POST['location'] ?? '');
$duration = normalize_text($_POST['duration'] ?? '');
$description = normalize_text($_POST['description'] ?? '');

if ($title === '' || $company === '' || $location === '' || $duration === '' || $description === '') {
    redirect_with_message('../html/dashboard.html', 'error', 'Tous les champs de l\'offre sont obligatoires.');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'INSERT INTO offers (title, company, location, duration, description) VALUES (:title, :company, :location, :duration, :description)'
    );
    $statement->execute([
        'title' => $title,
        'company' => $company,
        'location' => $location,
        'duration' => $duration,
        'description' => $description,
    ]);

    redirect_with_message('../html/dashboard.html', 'success', 'L\'offre a été ajoutée avec succès.');
} catch (PDOException $exception) {
    redirect_with_message('../html/dashboard.html', 'error', 'Impossible d\'ajouter l\'offre.');
}
