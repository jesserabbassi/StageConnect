<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();
require_login('../html/login.html');

if (is_admin()) {
    redirect_with_message('../html/offres.html', 'error', 'Les administrateurs ne peuvent pas postuler aux offres.');
}

$offerId = filter_input(INPUT_POST, 'offer_id', FILTER_VALIDATE_INT);
$user = current_user();
$userId = (int) ($user['id'] ?? 0);

if (!$offerId || $userId <= 0) {
    redirect_with_message('../html/offres.html', 'error', 'Offre invalide.');
}

try {
    $pdo = getPDO();

    $offerStatement = $pdo->prepare('SELECT id FROM offers WHERE id = :id LIMIT 1');
    $offerStatement->execute(['id' => $offerId]);
    if (!$offerStatement->fetch()) {
        redirect_with_message('../html/offres.html', 'error', 'Cette offre n\'existe plus.');
    }

    $portfolioStatement = $pdo->prepare(
        'SELECT cv_path FROM portfolios WHERE user_id = :user_id LIMIT 1'
    );
    $portfolioStatement->execute(['user_id' => $userId]);
    $portfolio = $portfolioStatement->fetch();

    if (!$portfolio || empty($portfolio['cv_path'])) {
        redirect_with_message('../html/portfolio.html', 'error', 'Veuillez compléter votre portfolio et ajouter un CV PDF avant de postuler.');
    }

    $duplicateStatement = $pdo->prepare(
        'SELECT id FROM applications WHERE user_id = :user_id AND offer_id = :offer_id LIMIT 1'
    );
    $duplicateStatement->execute([
        'user_id' => $userId,
        'offer_id' => $offerId,
    ]);

    if ($duplicateStatement->fetch()) {
        redirect_with_message('../html/offres.html', 'info', 'Vous avez déjà postulé à cette offre.');
    }

    $insertStatement = $pdo->prepare(
        'INSERT INTO applications (user_id, offer_id, status) VALUES (:user_id, :offer_id, :status)'
    );
    $insertStatement->execute([
        'user_id' => $userId,
        'offer_id' => $offerId,
        'status' => 'pending',
    ]);

    redirect_with_message('../html/portfolio.html', 'success', 'Votre candidature a bien été enregistrée.');
} catch (PDOException $exception) {
    redirect_with_message('../html/offres.html', 'error', 'Impossible d\'enregistrer la candidature.');
}
