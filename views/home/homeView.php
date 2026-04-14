<?php ob_start(); ?>

<h1>HOME</h1>

<p><a href="index.php?p=addTicketView">Créer un ticket</a></p>
<p><a href="index.php?p=listTicketsView">liste des tickets</a></p>

<?php
$content = ob_get_clean();
$title = "Accueil";
require('views/layout/baseLayout.php');
?>