<?php

function requireAuth(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user'])) {
        header('Location: index.php?p=connexionView');
        exit;
    }
}

function requireAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user'])) {
        header('Location: index.php?p=connexionView'); exit;
    }
    if ((int)$_SESSION['user']['role'] !== 1) {
        header('Location: index.php?p=home'); exit;
    }
}

function csrfGenerate(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrfCheck(): void
{
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        http_response_code(403);
        die('Requête invalide — jeton CSRF manquant ou expiré.');
    }
    // Renouvellement après usage
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
