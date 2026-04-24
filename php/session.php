<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$user = current_user();

json_response([
    'authenticated' => $user !== null,
    'role' => $user['role'] ?? 'guest',
    'full_name' => $user['full_name'] ?? '',
    'email' => $user['email'] ?? '',
    'user_id' => $user['id'] ?? null,
]);
