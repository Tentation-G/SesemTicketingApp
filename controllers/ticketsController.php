<?php
require_once('./models/ticketsModels.php');

// Liste tickets View
function listTicketsView(): void
{
    requireAuth();

    $user     = $_SESSION['user'];
    $role     = (int)$user['role'];
    $idUser   = (int)$user['id'];
    $idEquipe = !empty($user['equipe']) ? (int)$user['equipe'] : null;

    $filters = [
        'statut'   => $_GET['statut']   ?? '',
        'type'     => $_GET['type']     ?? '',
        'priorite' => $_GET['priorite'] ?? '',
        'search'   => trim($_GET['search'] ?? ''),
        'sort'     => $_GET['sort']     ?? 'IDTicket',
        'dir'      => $_GET['dir']      ?? 'DESC',
    ];

    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;

    $result      = getTickets($role, $idUser, $idEquipe, $filters, $page, $perPage);
    $tickets     = $result['tickets'];
    $total       = $result['total'];
    $totalPages  = (int)ceil($total / $perPage);

    $statutOptions   = getStatutOptions();
    $prioriteOptions = getPrioriteOptions();
    $typeOptions     = getEnumValues('tickets', 'Type');

    require_once('views/tickets/listTicketsView.php');
}

// Detail View
function ticketDetailView(): void
{
    requireAuth();
    csrfGenerate();

    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        //echo '<script>console.log("pas id")</script>';
        header('Location: index.php?p=listTicketsView');
        exit;
    }

    $ticket = getTicketById($id);
    if (!$ticket) {
        //echo '<script>console.log("pas ticket")</script>';
        header('Location: index.php?p=listTicketsView');
        exit;
    }

    $commentaires    = getTicketCommentaires($id);
    $techniciens     = getTechniciens();
    $statutOptions   = getStatutOptions();
    $prioriteOptions = getPrioriteOptions();
    $success         = $_GET['success'] ?? '';

    require_once('views/tickets/ticketDetailView.php');
}

// Action : Changer le statut du ticket
function updateStatut(): void
{
    requireAuth();
    csrfCheck();

    $id     = (int)($_POST['idTicket'] ?? 0);
    $statut = (int)($_POST['statut']   ?? 0);

    if ($id && array_key_exists($statut, getStatutOptions())) {
        try {
            updateTicketStatut($id, $statut);
        } catch (Exception $e) {
            error_log('[updateStatut] ' . $e->getMessage());
        }
    }

    header("Location: index.php?p=ticketDetailView&id=$id&success=statut");
    exit;
}

// Action : Assignation Tech ticket
function assignTechnicien(): void
{
    requireAuth();
    csrfCheck();

    $id     = (int)($_POST['idTicket'] ?? 0);
    $idUser = !empty($_POST['idUser']) ? (int)$_POST['idUser'] : null;

    if ($id) {
        try {
            assignTicket($id, $idUser);
        } catch (Exception $e) {
            error_log('[assignTechnicien] ' . $e->getMessage());
        }
    }

    header("Location: index.php?p=ticketDetailView&id=$id&success=assign");
    exit;
}

// Action : Ajouter commentaire
function addCommentaireAction(): void
{
    requireAuth();
    csrfCheck();

    $id      = (int)($_POST['idTicket'] ?? 0);
    $contenu = trim($_POST['contenu']   ?? '');
    $isNote  = !empty($_POST['isNote']);
    $idUser  = (int)$_SESSION['user']['id'];

    if ($id && $contenu !== '') {
        try {
            addCommentaire($id, $idUser, $contenu, $isNote);
        } catch (Exception $e) {
            error_log('[addCommentaire] ' . $e->getMessage());
        }
    }

    header("Location: index.php?p=ticketDetailView&id=$id&success=comment#commentaires");
    exit;
}

// Action : Cloturer Ticket
function cloturerTicketAction(): void
{
    requireAuth();
    csrfCheck();

    $role = (int)$_SESSION['user']['role'];
    if ($role > 2) {
        header('Location: index.php?p=listTicketsView');
        exit;
    }

    $id = (int)($_POST['idTicket'] ?? 0);
    if ($id) {
        try {
            cloturerTicket($id);
        } catch (Exception $e) {
            error_log('[cloturerTicket] ' . $e->getMessage());
        }
    }

    header("Location: index.php?p=ticketDetailView&id=$id&success=cloture");
    exit;
}

// Action : Supp ticket (Admin)
function deleteTicketAction(): void
{
    requireAuth();
    csrfCheck();

    if ((int)$_SESSION['user']['role'] !== 1) {
        header('Location: index.php?p=listTicketsView');
        exit;
    }

    $id = (int)($_POST['idTicket'] ?? 0);
    if ($id) {
        try {
            deleteTicket($id);
        } catch (Exception $e) {
            error_log('[deleteTicket] ' . $e->getMessage());
        }
    }

    header('Location: index.php?p=listTicketsView');
    exit;
}

// FORMULAIRE CRÉATION

function addTicketView(): void
{
    requireAuth();
    csrfGenerate();
    extract(loadTicketFormOptions());
    require_once('views/tickets/addTicketView.php');
}

function addTicket(): void
{
    requireAuth();
    extract(loadTicketFormOptions());
    csrfCheck();

    $required = ['type', 'priorite', 'nomClient', 'adresse', 'codePostal', 'ville'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
            $error = "Merci de remplir tous les champs obligatoires.";
            require_once('views/tickets/addTicketView.php');
            return;
        }
    }

    $data = [
        'type'            => trim($_POST['type']),
        'idUser'          => (int)$_SESSION['user']['id'],
        'idService'       => !empty($_SESSION['user']['service']) ? (int)$_SESSION['user']['service'] : null,
        'idEquipe'        => !empty($_SESSION['user']['equipe'])  ? (int)$_SESSION['user']['equipe']  : null,
        'priorite'        => (int)$_POST['priorite'],
        'nomClient'       => trim($_POST['nomClient']),
        'adresse'         => trim($_POST['adresse']),
        'codePostal'      => trim($_POST['codePostal']),
        'ville'           => trim($_POST['ville']),
        'adresseChantier' => trim($_POST['adresseChantier'] ?? ''),
        'numSite'         => trim($_POST['numSite']         ?? ''),
        'numContrat'      => trim($_POST['numContrat']      ?? ''),
        'idObjet'         => !empty($_POST['idObjet'])      ? (int)$_POST['idObjet']      : null,
        'idTypeClient'    => !empty($_POST['idTypeClient']) ? (int)$_POST['idTypeClient'] : null,
        'idMarque'        => !empty($_POST['idMarque'])     ? (int)$_POST['idMarque']     : null,
        'idFamille'       => !empty($_POST['idFamille'])    ? (int)$_POST['idFamille']    : null,
        'commentaire'     => trim($_POST['commentaire']     ?? ''),
    ];

    if (!in_array($data['type'], $typeOptions, true)) { $error = "Type invalide."; require_once('views/tickets/addTicketView.php'); return; }
    if (!array_key_exists($data['priorite'], $prioriteOptions)) { $error = "Priorité invalide."; require_once('views/tickets/addTicketView.php'); return; }

    try {
        if (createTicket($data)) { header('Location: index.php?p=listTicketsView'); exit; }
        $error = "Erreur lors de la création du ticket.";
    } catch (Exception $e) {
        error_log('[createTicket] ' . $e->getMessage());
        $error = "Une erreur inattendue est survenue.";
    }

    require_once('views/tickets/addTicketView.php');
}

// ACTION : Editer commentaire ticket
function editCommentaireAction(): void
{
    requireAuth();
    csrfCheck();

    $idCommentaire = (int)($_POST['idCommentaire'] ?? 0);
    $idTicket      = (int)($_POST['idTicket']      ?? 0);
    $contenu       = trim($_POST['contenu']         ?? '');
    $idUser        = (int)$_SESSION['user']['id'];

    if ($idCommentaire && $idTicket && $contenu !== '') {
        try {
            editCommentaire($idCommentaire, $idUser, $contenu);
        } catch (Exception $e) {
            error_log('[editCommentaire] ' . $e->getMessage());
        }
    }

    header("Location: index.php?p=ticketDetailView&id=$idTicket&success=edited#commentaires");
    exit;
}