<?php

require ('core/config.php');
require ('core/function.php');
require ('core/debug.php');

require('controllers/othersController.php');
require('controllers/userController.php');
require('controllers/ticketsController.php');

//http://localhost/projets/MVC_isesemTickets/?p=connexion
if(isset($_GET['p'])){

    switch ($_GET['p']) {
         /* == Sign in / log in / log out == */
        // Affichage Vue
        case 'inscriptionView':
                inscriptionView();
            break;
        // Back
        case 'inscription':
                inscription();
            break;

        // Affichage Vue
        case 'connexionView':
                connexionView();
            break;
        // Back
        case 'connexion':
                connexion();
            break;
        
        // Back
        case 'logout':
                logout();
            break;

        /* == Ticket == */
        case 'addTicketView':
                addTicketView();
            break;

        case 'addTicket':
                addTicket();
            break;

        case 'home':
                homeView();
            break;

        default:
                page404();
            break;
    }
    
}else{
    //echo "Aucun paramètre d'url défini";
    homeView();
    //page404();
}

?>