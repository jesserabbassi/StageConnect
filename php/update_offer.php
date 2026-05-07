<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();
require_admin_redirect('../html/dashboard.html');

$offerId = filter_input(INPUT_POST, 'offer_id', FILTER_VALIDATE_INT);
$title = normalize_text($_POST['title'] ?? '');
$company = normalize_text($_POST['company'] ?? '');
$location = normalize_text($_POST['location'] ?? '');
$duration = normalize_text($_POST['duration'] ?? '');
$description = normalize_text($_POST['description'] ?? '');

if (!$offerId) {
    redirect_with_message('../html/dashboard.html', 'error', 'Identifiant d\'offre invalide.');
}

if ($title === '' || $company === '' || $location === '' || $duration === '' || $description === '') {
    redirect_with_message('../html/dashboard.html', 'error', 'Tous les champs de l\'offre sont obligatoires.');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'UPDATE offers
         SET title = :title,
             company = :company,
             location = :location,
             duration = :duration,
             description = :description
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $offerId,
        'title' => $title,
        'company' => $company,
        'location' => $location,
        'duration' => $duration,
        'description' => $description,
    ]);

    redirect_with_message('../html/dashboard.html', 'success', 'L\'offre a ete modifiee avec succes.');
} catch (PDOException $exception) {
    redirect_with_message('../html/dashboard.html', 'error', 'Impossible de modifier cette offre.');
}
