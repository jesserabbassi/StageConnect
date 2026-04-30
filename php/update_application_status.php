<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();
require_admin_redirect('../html/dashboard.html');

$applicationId = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
$status = normalize_text($_POST['status'] ?? '');
$allowedStatuses = ['accepted', 'rejected', 'pending'];

if (!$applicationId || !in_array($status, $allowedStatuses, true)) {
    redirect_with_message('../html/dashboard.html', 'error', 'Demande invalide.');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare('UPDATE applications SET status = :status WHERE id = :id');
    $statement->execute([
        'id' => $applicationId,
        'status' => $status,
    ]);

    redirect_with_message('../html/dashboard.html', 'success', 'Le statut de la candidature a ete mis a jour.');
} catch (PDOException $exception) {
    redirect_with_message('../html/dashboard.html', 'error', 'Impossible de mettre a jour cette candidature.');
}
