<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();
require_admin_redirect('../html/dashboard.html');

$offerId = filter_input(INPUT_POST, 'offer_id', FILTER_VALIDATE_INT);

if (!$offerId) {
    redirect_with_message('../html/dashboard.html', 'error', 'Identifiant d\'offre invalide.');
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare('DELETE FROM offers WHERE id = :id');
    $statement->execute(['id' => $offerId]);

    redirect_with_message('../html/dashboard.html', 'success', 'L\'offre a été supprimée.');
} catch (PDOException $exception) {
    redirect_with_message('../html/dashboard.html', 'error', 'Impossible de supprimer cette offre.');
}
