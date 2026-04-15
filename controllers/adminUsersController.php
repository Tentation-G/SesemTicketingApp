<?php
require_once('./models/usersModel.php');

/* ============================================================
    HELPERS
   ============================================================ */

//require_once('./core/function.php');

/* ============================================================
    LISTE + FORMULAIRES (une seule page pour tout voir)
   ============================================================ */

function usersView(): void
{
    requireAdmin();
    csrfGenerate();

    $filters = [
        'search' => trim($_GET['search'] ?? ''),
        'role'   => $_GET['role']        ?? '',
        'actif'  => $_GET['actif']       ?? '',
    ];
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $result     = getAllUsers($filters, $page, $perPage);
    $users      = $result['users'];
    $total      = $result['total'];
    $totalPages = (int)ceil($total / $perPage);

    $roleOptions = getRoleOptions();
    $equipes     = getEquipes();
    $services    = getServices();
    $success     = $_GET['success'] ?? '';
    $error       = $_GET['error']   ?? '';

    // Pré-remplissage du formulaire de création après erreur
    $createData  = $_SESSION['create_form_data'] ?? [];
    unset($_SESSION['create_form_data']);

    // Données du formulaire d'édition après erreur
    $editData    = $_SESSION['edit_form_data']   ?? [];
    unset($_SESSION['edit_form_data']);

    require_once('views/admin/usersView.php');
}

/* ============================================================
    CRÉATION
   ============================================================ */

function createUserAction(): void
{
    requireAdmin();
    csrfCheck();

    $required = ['login', 'password', 'nom', 'prenom', 'email', 'role'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $_SESSION['create_form_data'] = $_POST;
            header('Location: index.php?p=usersView&error=required');
            exit;
        }
    }

    if (loginExists(trim($_POST['login']))) {
        $_SESSION['create_form_data'] = $_POST;
        header('Location: index.php?p=usersView&error=login_taken');
        exit;
    }

    try {
        $ok = createUser([
            'login'    => trim($_POST['login']),
            'password' => $_POST['password'],
            'nom'      => trim($_POST['nom']),
            'prenom'   => trim($_POST['prenom']),
            'email'    => trim($_POST['email']),
            'role'     => (int)$_POST['role'],
            'service'  => $_POST['service'] ?? null,
            'equipe'   => $_POST['equipe']  ?? null,
            'actif'    => isset($_POST['actif']) ? 1 : 0,
        ]);
        header('Location: index.php?p=usersView&success=created');
    } catch (Exception $e) {
        error_log('[createUser] ' . $e->getMessage());
        header('Location: index.php?p=usersView&error=server');
    }
    exit;
}

/* ============================================================
    PAGE D'ÉDITION D'UN UTILISATEUR
   ============================================================ */

function editUserView(): void
{
    requireAdmin();
    csrfGenerate();

    $id   = (int)($_GET['id'] ?? 0);
    $user = $id ? getUserById($id) : false;

    if (!$user) {
        header('Location: index.php?p=usersView');
        exit;
    }

    $roleOptions = getRoleOptions();
    $equipes     = getEquipes();
    $services    = getServices();
    $error       = $_GET['error']   ?? '';
    $success     = $_GET['success'] ?? '';

    // Pré-remplissage après erreur de validation
    $formData    = $_SESSION['edit_form_data'] ?? [];
    unset($_SESSION['edit_form_data']);

    require_once('views/admin/editUserView.php');
}

/* ============================================================
    TRAITEMENT ÉDITION
   ============================================================ */

function updateUserAction(): void
{
    requireAdmin();
    csrfCheck();

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header('Location: index.php?p=usersView');
        exit;
    }

    $required = ['nom', 'prenom', 'email', 'role'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $_SESSION['edit_form_data'] = $_POST;
            header("Location: index.php?p=editUserView&id=$id&error=required");
            exit;
        }
    }

    try {
        updateUser($id, [
            'nom'      => trim($_POST['nom']),
            'prenom'   => trim($_POST['prenom']),
            'email'    => trim($_POST['email']),
            'role'     => (int)$_POST['role'],
            'service'  => $_POST['service']  ?? null,
            'equipe'   => $_POST['equipe']   ?? null,
            'actif'    => isset($_POST['actif']) ? 1 : 0,
            'password' => $_POST['password'] ?? '',
        ]);
        header("Location: index.php?p=editUserView&id=$id&success=saved");
    } catch (Exception $e) {
        error_log('[updateUser] ' . $e->getMessage());
        header("Location: index.php?p=editUserView&id=$id&error=server");
    }
    exit;
}

/* ============================================================
    TOGGLE ACTIF / INACTIF
   ============================================================ */

function toggleUserAction(): void
{
    requireAdmin();
    csrfCheck();

    $id = (int)($_POST['id'] ?? 0);

    // Un admin ne peut pas se désactiver lui-même
    if ($id && $id !== (int)$_SESSION['user']['id']) {
        try {
            toggleUserActif($id);
        } catch (Exception $e) {
            error_log('[toggleUser] ' . $e->getMessage());
        }
    }

    $redirect = $_POST['redirect'] ?? 'index.php?p=usersView';
    header('Location: ' . $redirect . '&success=toggled');
    exit;
}

/* ============================================================
    SUPPRESSION
   ============================================================ */

function deleteUserAction(): void
{
    requireAdmin();
    csrfCheck();

    $id = (int)($_POST['id'] ?? 0);

    if ($id && $id !== (int)$_SESSION['user']['id']) {
        try {
            deleteUser($id);
        } catch (Exception $e) {
            error_log('[deleteUser] ' . $e->getMessage());
            header('Location: index.php?p=usersView&error=server');
            exit;
        }
    }

    header('Location: index.php?p=usersView&success=deleted');
    exit;
}