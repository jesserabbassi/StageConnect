<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

require_post();
require_login('../html/login.html');

$user = current_user();
$userId = (int) ($user['id'] ?? 0);

$fullName = normalize_text($_POST['full_name'] ?? '');
$email = strtolower(normalize_text($_POST['email'] ?? ''));
$phone = normalize_text($_POST['phone'] ?? '');
$bio = normalize_text($_POST['bio'] ?? '');
$skills = normalize_text($_POST['skills'] ?? '');
$education = normalize_text($_POST['education'] ?? '');
$experience = normalize_text($_POST['experience'] ?? '');
$languages = normalize_text($_POST['languages'] ?? '');

if ($userId <= 0 || $fullName === '' || $email === '') {
    redirect_with_message('../html/portfolio.html', 'error', 'Le nom complet et l\'email sont obligatoires.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_message('../html/portfolio.html', 'error', 'Adresse email invalide.');
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
    $emailCheck->execute([
        'email' => $email,
        'id' => $userId,
    ]);

    if ($emailCheck->fetch()) {
        $pdo->rollBack();
        redirect_with_message('../html/portfolio.html', 'error', 'Cette adresse email est déjà utilisée par un autre compte.');
    }

    $currentPortfolioStatement = $pdo->prepare('SELECT cv_path FROM portfolios WHERE user_id = :user_id LIMIT 1');
    $currentPortfolioStatement->execute(['user_id' => $userId]);
    $currentPortfolio = $currentPortfolioStatement->fetch();

    $cvPath = $currentPortfolio['cv_path'] ?? null;
    if (isset($_FILES['cv'])) {
        $uploadedPath = upload_cv($_FILES['cv']);
        if ($uploadedPath !== null) {
            $cvPath = $uploadedPath;
        }
    }

    $updateUser = $pdo->prepare(
        'UPDATE users SET full_name = :full_name, email = :email WHERE id = :id'
    );
    $updateUser->execute([
        'full_name' => $fullName,
        'email' => $email,
        'id' => $userId,
    ]);

    $savePortfolio = $pdo->prepare(
        'INSERT INTO portfolios (user_id, phone, bio, skills, education, experience, languages, cv_path)
         VALUES (:user_id, :phone, :bio, :skills, :education, :experience, :languages, :cv_path)
         ON DUPLICATE KEY UPDATE
            phone = VALUES(phone),
            bio = VALUES(bio),
            skills = VALUES(skills),
            education = VALUES(education),
            experience = VALUES(experience),
            languages = VALUES(languages),
            cv_path = VALUES(cv_path),
            updated_at = CURRENT_TIMESTAMP'
    );
    $savePortfolio->execute([
        'user_id' => $userId,
        'phone' => $phone,
        'bio' => $bio,
        'skills' => $skills,
        'education' => $education,
        'experience' => $experience,
        'languages' => $languages,
        'cv_path' => $cvPath,
    ]);

    $pdo->commit();
    refresh_session_user($pdo, $userId);

    redirect_with_message('../html/portfolio.html', 'success', 'Portfolio enregistré avec succès.');
} catch (RuntimeException $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with_message('../html/portfolio.html', 'error', $exception->getMessage());
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with_message('../html/portfolio.html', 'error', 'Impossible d\'enregistrer le portfolio.');
}
