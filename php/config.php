<?php

declare(strict_types=1);

function loadEnvFile(string $filePath): void
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        if ($trimmedLine === '' || str_starts_with($trimmedLine, '#')) {
            continue;
        }

        $parts = explode('=', $trimmedLine, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $name = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");

        if ($name === '') {
            continue;
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

loadEnvFile(dirname(__DIR__) . '/.env');

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
define('GROQ_API_KEY', getenv('GROQ_API_KEY') ?: '');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('CHATBOT_MAX_MESSAGE_LENGTH', 500);
define('CHATBOT_CONTEXT_LIMIT', 3);
