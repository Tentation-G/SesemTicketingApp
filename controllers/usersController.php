<?php
require_once('models/usersModel.php');

function connexionView(): void
{
    require_once('views/user/connexionView.php');
}

function inscriptionView(): void
{
    require_once('views/user/inscriptionView.php');
}

function homeView(): void
{
    require_once('views/home/homeView.php');
}

function connexion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_POST['login']) || empty($_POST['pass'])) {
        $error = "Identifiants incorrects.";
        require_once __DIR__ . '/../views/user/connexionView.php';
        return;
    }

    $login = trim($_POST['login']);
    $user  = getUserByLogin($login);

    if (empty($user) || empty($user['pass'])) {
        $error = "Identifiants incorrects.";
        require_once __DIR__ . '/../views/user/connexionView.php';
        return;
    }

    if (!password_verify($_POST['pass'], $user['pass'])) {
        $error = "Identifiants incorrects.";
        require_once __DIR__ . '/../views/user/connexionView.php';
        return;
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'      => $user['id'],
        'login'   => $user['login'],
        'nom'     => $user['nom'],
        'prenom'  => $user['prenom'],
        'role'    => $user['role'],
        'service' => $user['service'],
        'equipe'  => $user['equipe'],
    ];

    header('Location: index.php?p=home');
    exit;
}

function inscription(): void
{
    if (
        empty($_POST['login'])        ||
        empty($_POST['pass'])         ||
        empty($_POST['pass_confirm']) ||
        empty($_POST['nom'])          ||
        empty($_POST['prenom'])       ||
        empty($_POST['email'])
    ) {
        $error = "Tous les champs sont obligatoires.";
        require_once 'views/user/inscriptionView.php';
        return;
    }

    $login       = trim($_POST['login']);
    $password    = $_POST['pass'];
    $passConfirm = $_POST['pass_confirm'];
    $nom         = trim($_POST['nom']);
    $prenom      = trim($_POST['prenom']);
    $email       = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
        require_once 'views/user/inscriptionView.php';
        return;
    }

    if ($password !== $passConfirm) {
        $error = "Les mots de passe ne correspondent pas.";
        require_once 'views/user/inscriptionView.php';
        return;
    }

    if (strlen($password) < 3) {
        $error = "Le mot de passe doit contenir au moins 3 caractères.";
        require_once 'views/user/inscriptionView.php';
        return;
    }

    if (getUserByLogin($login)) {
        $error = "Ce login existe déjà.";
        require_once 'views/user/inscriptionView.php';
        return;
    }

    // createUser attend maintenant un tableau (nouveau modèle)
    $success = createUser([
        'login'    => $login,
        'password' => $password,
        'nom'      => $nom,
        'prenom'   => $prenom,
        'email'    => $email,
        'role'     => 3,      // Collaborateur par défaut à l'inscription
        'service'  => null,
        'equipe'   => null,
        'actif'    => 1,
    ]);

    if ($success) {
        header('Location: index.php?p=connexionView');
        exit;
    }

    $error = "Une erreur est survenue lors de l'inscription.";
    require_once 'views/user/inscriptionView.php';
}

function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '',
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