<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

try {
    $pdo = getPDO();
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
    $limitSql = ($limit && $limit > 0) ? ' LIMIT ' . min($limit, 20) : '';

    $statement = $pdo->query(
        'SELECT id, title, company, location, duration, description, created_at
         FROM offers
         ORDER BY created_at DESC' . $limitSql
    );
    $offers = $statement->fetchAll();

    $appliedMap = [];
    if (is_authenticated()) {
        $user = current_user();
        $appliedStatement = $pdo->prepare('SELECT offer_id FROM applications WHERE user_id = :user_id');
        $appliedStatement->execute(['user_id' => $user['id']]);
        foreach ($appliedStatement->fetchAll() as $row) {
            $appliedMap[(int) $row['offer_id']] = true;
        }
    }

    $offers = array_map(static function (array $offer) use ($appliedMap): array {
        $offer['id'] = (int) $offer['id'];
        $offer['has_applied'] = isset($appliedMap[$offer['id']]);
        return $offer;
    }, $offers);

    json_response([
        'offers' => $offers,
    ]);
} catch (PDOException $exception) {
    json_response([
        'message' => 'Impossible de charger les offres.',
    ], 500);
}
