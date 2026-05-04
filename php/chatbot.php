<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Methode non autorisee.']);
    exit;
}

if (GROQ_API_KEY === '') {
    http_response_code(500);
    echo json_encode(['message' => "La cle API Groq n'est pas configuree cote serveur."]);
    exit;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput ?: '', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['message' => 'Requete invalide.']);
    exit;
}

$message = trim((string) ($payload['message'] ?? ''));
$message = strip_tags($message);
$message = preg_replace('/\s+/', ' ', $message ?? '');

if ($message === '') {
    http_response_code(422);
    echo json_encode(['message' => 'Veuillez saisir un message.']);
    exit;
}

if (mb_strlen($message) > CHATBOT_MAX_MESSAGE_LENGTH) {
    http_response_code(422);
    echo json_encode(['message' => 'Votre message est trop long.']);
    exit;
}

if (!isset($_SESSION['chatbot_history']) || !is_array($_SESSION['chatbot_history'])) {
    $_SESSION['chatbot_history'] = [];
}

$systemPrompt = <<<'PROMPT'
You are a helpful assistant for StageConnect, a student internship platform.
Your job is to help students use the platform and answer questions about:
- how to apply for an internship
- how to fill in their portfolio
- how to upload a CV
- general internship advice

Guidelines:
- Keep answers short, clear, and practical.
- Prefer French when the user writes in French.
- If the answer depends on StageConnect features, explain the likely steps based on a standard student workflow.
- Do not invent administrative rules or guarantees.
- If the platform-specific detail is unknown, say so briefly and give safe general guidance.
PROMPT;

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
];

$history = array_slice($_SESSION['chatbot_history'], -CHATBOT_CONTEXT_LIMIT * 2);
foreach ($history as $historyMessage) {
    if (
        is_array($historyMessage)
        && isset($historyMessage['role'], $historyMessage['content'])
        && in_array($historyMessage['role'], ['user', 'assistant'], true)
    ) {
        $messages[] = [
            'role' => $historyMessage['role'],
            'content' => (string) $historyMessage['content'],
        ];
    }
}

$messages[] = ['role' => 'user', 'content' => $message];

$requestBody = json_encode([
    'model' => 'llama-3.3-70b-versatile',
    'messages' => $messages,
    'temperature' => 0.4,
    'max_tokens' => 400,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($requestBody === false) {
    http_response_code(500);
    echo json_encode(['message' => 'Impossible de preparer la requete AI.']);
    exit;
}

$curl = curl_init(GROQ_API_URL);
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
    ],
    CURLOPT_POSTFIELDS => $requestBody,
    CURLOPT_TIMEOUT => 30,
]);

$apiResponse = curl_exec($curl);
$curlError = curl_error($curl);
$httpCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
curl_close($curl);

if ($apiResponse === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode(['message' => "Impossible de contacter l'API Groq pour le moment."]);
    exit;
}

$apiData = json_decode($apiResponse, true);
$assistantMessage = trim((string) ($apiData['choices'][0]['message']['content'] ?? ''));

if ($httpCode >= 400 || $assistantMessage === '') {
    http_response_code(502);
    echo json_encode([
        'message' => "Le chatbot n'a pas pu generer de reponse pour le moment.",
    ]);
    exit;
}

$_SESSION['chatbot_history'][] = ['role' => 'user', 'content' => $message];
$_SESSION['chatbot_history'][] = ['role' => 'assistant', 'content' => $assistantMessage];
$_SESSION['chatbot_history'] = array_slice($_SESSION['chatbot_history'], -CHATBOT_CONTEXT_LIMIT * 2);

echo json_encode([
    'reply' => $assistantMessage,
]);
