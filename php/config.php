<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Tunis');

if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = dirname(__DIR__) . '/tmp/sessions';

    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }

    session_save_path($sessionPath);
    session_name('stageconnect_session');
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'stageconnect');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_NAME', 'StageConnect');
define('MAX_CV_SIZE', 2 * 1024 * 1024);
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/cv/');
define('UPLOAD_RELATIVE_DIR', 'uploads/cv/');
