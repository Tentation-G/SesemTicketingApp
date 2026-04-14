<?php
require('./models/ticketsModels.php');

/* ============================================================
    LISTE DES TICKETS
   ============================================================ */

function listTicketsView()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user'])) {
        header('Location: index.php?p=connexionView');
        exit;
    }

    $user     = $_SESSION['user'];
    $role     = (int) $user['role'];
    $idUser   = (int) $user['id'];
    $idEquipe = !empty($user['equipe']) ? (int) $user['equipe'] : null;

    // Filtres GET (valeurs par défaut = vide = aucun filtre)
    $filters = [
        'statut'   => $_GET['statut']   ?? '',
        'type'     => $_GET['type']     ?? '',
        'priorite' => $_GET['priorite'] ?? '',
        'search'   => trim($_GET['search'] ?? ''),
        'sort'     => $_GET['sort']     ?? 'IDTicket',
        'dir'      => $_GET['dir']      ?? 'DESC',
    ];

    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 20;

    $result  = getTickets($role, $idUser, $idEquipe, $filters, $page, $perPage);

    $tickets       = $result['tickets'];
    $total         = $result['total'];
    $totalPages    = (int) ceil($total / $perPage);

    $statutOptions   = getStatutOptions();
    $prioriteOptions = getPrioriteOptions();
    $typeOptions     = getEnumValues('tickets', 'Type');

    require('views/tickets/listTicketView.php');
    
}

/* ============================================================
    FORMULAIRE CRÉATION
   ============================================================ */

function addTicketView()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user'])) {
        header('Location: index.php?p=connexionView');
        exit;
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    extract(loadTicketFormOptions());
    require('views/tickets/addTicketView.php');
}

/* ============================================================
    TRAITEMENT CRÉATION
   ============================================================ */

function addTicket()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user'])) {
        header('Location: index.php?p=connexionView');
        exit;
    }

    extract(loadTicketFormOptions());

    // Vérification CSRF
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        $error = "Requête invalide, veuillez réessayer.";
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        require('views/tickets/addTicketView.php');
        return;
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Validation champs obligatoires
    $required = ['type', 'priorite', 'nomClient', 'adresse', 'codePostal', 'ville'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || trim((string) $_POST[$field]) === '') {
            $error = "Merci de remplir tous les champs obligatoires.";
            require('views/tickets/addTicketView.php');
            return;
        }
    }

    $data = [
        'type'            => trim($_POST['type']),
        'idUser'          => (int) $_SESSION['user']['id'],
        'idService'       => !empty($_SESSION['user']['service']) ? (int) $_SESSION['user']['service'] : null,
        'idEquipe'        => !empty($_SESSION['user']['equipe'])  ? (int) $_SESSION['user']['equipe']  : null,
        'priorite'        => (int) $_POST['priorite'],
        'nomClient'       => trim($_POST['nomClient']),
        'adresse'         => trim($_POST['adresse']),
        'codePostal'      => trim($_POST['codePostal']),
        'ville'           => trim($_POST['ville']),
        'adresseChantier' => trim($_POST['adresseChantier'] ?? ''),
        'numSite'         => trim($_POST['numSite']         ?? ''),
        'numContrat'      => trim($_POST['numContrat']      ?? ''),
        'idObjet'         => !empty($_POST['idObjet'])      ? (int) $_POST['idObjet']      : null,
        'idTypeClient'    => !empty($_POST['idTypeClient']) ? (int) $_POST['idTypeClient'] : null,
        'idMarque'        => !empty($_POST['idMarque'])     ? (int) $_POST['idMarque']     : null,
        'idFamille'       => !empty($_POST['idFamille'])    ? (int) $_POST['idFamille']    : null,
        'commentaire'     => trim($_POST['commentaire']     ?? ''),
    ];

    if (!in_array($data['type'], $typeOptions, true)) {
        $error = "Type invalide.";
        require('views/tickets/addTicketView.php');
        return;
    }

    if (!array_key_exists($data['priorite'], $prioriteOptions)) {
        $error = "Priorité invalide.";
        require('views/tickets/addTicketView.php');
        return;
    }

    try {
        if (createTicket($data)) {
            header('Location: index.php?p=listTicketsView');
            exit;
        }
        $error = "Erreur lors de la création du ticket.";
    } catch (Exception $e) {
        error_log('[createTicket] ' . $e->getMessage());
        $error = "Une erreur inattendue est survenue. Veuillez réessayer.";
    }

    require('views/tickets/addTicketView.php');
}