<?php

require_once('core/config.php');
require_once('core/function.php');
//require_once('core/debug.php');

require_once('controllers/othersController.php');
require_once('controllers/usersController.php');
require_once('controllers/ticketsController.php');

require_once('controllers/adminUsersController.php');  // <- CRUD admin

if (isset($_GET['p'])) {

    switch ($_GET['p']) {

        /* ══ Authentification ════════════════════════════════ */
        case 'inscriptionView':
            inscriptionView();
            break;
        case 'inscription':
            inscription();
            break;

        case 'connexionView':
            connexionView();
            break;
        case 'connexion':
            connexion();
            break;

        case 'logout':
            logout();
            break;

        /* ══ Tickets ══════════════════════════════════════════ */
        case 'addTicketView':
            addTicketView();
            break;
        case 'addTicket':
            addTicket();
            break;

        case 'listTicketsView':
            listTicketsView();
            break;

        case 'ticketDetailView':
            ticketDetailView();
            break;

        // Actions sur un ticket
        case 'updateStatut':
            updateStatut();
            break;
        case 'assignTechnicien':
            assignTechnicien();
            break;
        case 'addCommentaireAction':
            addCommentaireAction();
            break;
        case 'editCommentaireAction':
            editCommentaireAction();
            break;
        case 'cloturerTicketAction':
            cloturerTicketAction();
            break;
        case 'deleteTicketAction':
            deleteTicketAction();
            break;

        /* ══ Admin — utilisateurs ═════════════════════════════ */
        case 'usersView':
            usersView();
            break;
        case 'editUserView':
            editUserView();
            break;
        case 'createUserAction':
            createUserAction();
            break;
        case 'updateUserAction':
            updateUserAction();
            break;
        case 'toggleUserAction':
            toggleUserAction();
            break;
        case 'deleteUserAction':
            deleteUserAction();
            break;

        /* ══ Accueil ══════════════════════════════════════════ */
        case 'home':
            listTicketsView();
            break;

        case 'enConstructionBipBoop':
            enConstruction();
            break;

        default:
            enConstruction();
            break;
    }

} else {
    homeView();
}
?>