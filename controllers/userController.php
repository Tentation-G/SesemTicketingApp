<?php
require('models/usersModel.php');

function connexionView() {
    require('views/user/connexionView.php');
}

function inscriptionView() {
    require('views/user/inscriptionView.php');
}

function homeView() {
    require('views/home/homeView.php');
}



function connexion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_POST['login']) || empty($_POST['pass'])) {
        $error = "Identifiants incorrects";
        require __DIR__ . '/../views/user/connexionView.php';
        return;
    }

    $login = trim($_POST['login']);
    $password = $_POST['pass'];

    $user = getUserByLogin($login);

    echo '<pre>';
    print_r($user);
    echo '</pre>';

    if (empty($user)) {
        $error = "Identifiants incorrects";
        require __DIR__ . '/../views/user/connexionView.php';
        return;
    }

    if (empty($user['pass'])) {
        $error = "Identifiants incorrects";
        require __DIR__ . '/../views/user/connexionView.php';
        return;
    }

    if (!password_verify($password, $user['pass'])) {
        $error = "Identifiants incorrects";
        require __DIR__ . '/../views/user/connexionView.php';
        return;
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'login' => $user['login'],
        'nom' => $user['nom'],
        'prenom' => $user['prenom'],
        'role' => $user['role'],
        'service' => $user['service'],
        'equipe' => $user['equipe']
    ];

    header('Location: index.php?p=home');
    exit;
}



function inscription()
{
    $error = null;

    if (
        empty($_POST['login']) ||
        empty($_POST['pass']) ||
        empty($_POST['pass_confirm']) ||
        empty($_POST['nom']) ||
        empty($_POST['prenom']) ||
        empty($_POST['email'])
    ) {
        $error = "Tous les champs sont obligatoires.";
        require 'views/user/inscriptionView.php';
        return;
    }

    $login = trim($_POST['login']);
    $password = $_POST['pass'];
    $passConfirm = $_POST['pass_confirm'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
        require 'views/user/inscriptionView.php';
        return;
    }

    if ($password !== $passConfirm) {
        $error = "Les mots de passe ne correspondent pas.";
        require 'views/user/inscriptionView.php';
        return;
    }

    if (strlen($password) < 2) {
        $error = "Le mot de passe doit contenir au moins 3 (temp) caractères.";
        require 'views/user/inscriptionView.php';
        return;
    }

    $existingUser = getUserByLogin($login);

    if ($existingUser) {
        $error = "Ce login existe déjà.";
        require 'views/user/inscriptionView.php';
        return;
    }

    $success = createUser($login, $password, $nom, $prenom, $email);

    if ($success) {
        header('Location: index.php?p=connexionView');
        exit;
    }

    $error = "Une erreur est survenue lors de l'inscription.";
    require 'views/user/inscriptionView.php';
}



function logout()
{
    session_start();

    // supprimer le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    $_SESSION = [];
    session_destroy();

    header('Location: index.php?p=connexionView');
    exit;
}