<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_admin_json();

try {
    $pdo = getPDO();

    $offersCount = (int) $pdo->query('SELECT COUNT(*) FROM offers')->fetchColumn();
    $applicationsCount = (int) $pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn();
    $studentsCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

    $offers = $pdo->query(
        'SELECT id, title, company, location, duration, created_at
         FROM offers
         ORDER BY created_at DESC'
    )->fetchAll();

    $applications = $pdo->query(
        'SELECT a.status, a.created_at,
                u.full_name AS student_name,
                u.email AS student_email,
                o.title AS offer_title,
                o.company,
                p.cv_path
         FROM applications a
         INNER JOIN users u ON u.id = a.user_id
         INNER JOIN offers o ON o.id = a.offer_id
         LEFT JOIN portfolios p ON p.user_id = u.id
         ORDER BY a.created_at DESC'
    )->fetchAll();

    $applications = array_map(static function (array $application): array {
        $application['cv_url'] = frontend_asset_path($application['cv_path'] ?? null);
        unset($application['cv_path']);
        return $application;
    }, $applications);

    json_response([
        'stats' => [
            'offers_count' => $offersCount,
            'applications_count' => $applicationsCount,
            'students_count' => $studentsCount,
        ],
        'offers' => $offers,
        'applications' => $applications,
    ]);
} catch (PDOException $exception) {
    json_response([
        'message' => 'Impossible de charger les données du dashboard.',
    ], 500);
}
