<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

try {
    $pdo = getPDO();
    $requestedUserId = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT);
    $currentFlag = filter_input(INPUT_GET, 'current', FILTER_VALIDATE_INT);

    if ($requestedUserId) {
        $statement = $pdo->prepare(
            'SELECT u.id, u.full_name, u.email, p.phone, p.bio, p.skills, p.education, p.experience, p.languages, p.cv_path
             FROM users u
             LEFT JOIN portfolios p ON p.user_id = u.id
             WHERE u.id = :id AND u.role = :role
             LIMIT 1'
        );
        $statement->execute([
            'id' => $requestedUserId,
            'role' => 'student',
        ]);
        $portfolio = $statement->fetch();

        if (!$portfolio) {
            json_response([
                'message' => 'Portfolio étudiant introuvable.',
            ], 404);
        }

        json_response([
            'portfolio' => [
                'user_id' => (int) $portfolio['id'],
                'full_name' => $portfolio['full_name'],
                'email' => $portfolio['email'],
                'phone' => $portfolio['phone'] ?? '',
                'bio' => $portfolio['bio'] ?? '',
                'skills' => $portfolio['skills'] ?? '',
                'education' => $portfolio['education'] ?? '',
                'experience' => $portfolio['experience'] ?? '',
                'languages' => $portfolio['languages'] ?? '',
                'cv_url' => frontend_asset_path($portfolio['cv_path'] ?? null),
                'public_url' => build_public_portfolio_url((int) $portfolio['id']),
            ],
        ]);
    }

    if (!$currentFlag) {
        json_response([
            'message' => 'Requête invalide.',
        ], 400);
    }

    if (!is_authenticated()) {
        json_response([
            'message' => 'Connexion requise.',
        ], 401);
    }

    $user = current_user();
    $statement = $pdo->prepare(
        'SELECT u.id, u.full_name, u.email, p.phone, p.bio, p.skills, p.education, p.experience, p.languages, p.cv_path
         FROM users u
         LEFT JOIN portfolios p ON p.user_id = u.id
         WHERE u.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $user['id']]);
    $portfolio = $statement->fetch();

    if (!$portfolio) {
        json_response([
            'message' => 'Profil introuvable.',
        ], 404);
    }

    $applicationsStatement = $pdo->prepare(
        'SELECT a.status, a.created_at, o.title AS offer_title, o.company
         FROM applications a
         INNER JOIN offers o ON o.id = a.offer_id
         WHERE a.user_id = :user_id
         ORDER BY a.created_at DESC'
    );
    $applicationsStatement->execute(['user_id' => $user['id']]);
    $applications = $applicationsStatement->fetchAll();

    json_response([
        'portfolio' => [
            'user_id' => (int) $portfolio['id'],
            'full_name' => $portfolio['full_name'],
            'email' => $portfolio['email'],
            'phone' => $portfolio['phone'] ?? '',
            'bio' => $portfolio['bio'] ?? '',
            'skills' => $portfolio['skills'] ?? '',
            'education' => $portfolio['education'] ?? '',
            'experience' => $portfolio['experience'] ?? '',
            'languages' => $portfolio['languages'] ?? '',
            'cv_url' => frontend_asset_path($portfolio['cv_path'] ?? null),
            'public_url' => build_public_portfolio_url((int) $portfolio['id']),
        ],
        'applications' => $applications,
    ]);
} catch (PDOException $exception) {
    json_response([
        'message' => 'Impossible de charger le portfolio.',
    ], 500);
}
