<?php
require('./models/ticketsModels.php');

function addTicketView()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user'])) {
        header('Location: index.php?p=connexionView');
        exit;
    }

    extract(loadTicketFormOptions());
    require('views/tickets/addTicketView.php');
}

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

    if (
        empty($_POST['type']) ||
        empty($_POST['priorite']) ||
        empty($_POST['nomClient']) ||
        empty($_POST['adresse']) ||
        empty($_POST['codePostal']) ||
        empty($_POST['ville']) ||
        empty($_POST['commentaire'])
    ) {
        $error = "Merci de remplir tous les champs obligatoires.";
        require('views/tickets/addTicketView.php');
        return;
    }

    $data = [
        'type' => trim($_POST['type']),
        'idUser' => (int) $_SESSION['user']['id'],
        'idService' => !empty($_SESSION['user']['service']) ? (int) $_SESSION['user']['service'] : null,
        'idEquipe' => !empty($_SESSION['user']['equipe']) ? (int) $_SESSION['user']['equipe'] : null,
        'priorite' => (int) $_POST['priorite'],
        'nomClient' => trim($_POST['nomClient']),
        'adresse' => trim($_POST['adresse']),
        'codePostal' => trim($_POST['codePostal']),
        'ville' => trim($_POST['ville']),
        'adresseChantier' => trim($_POST['adresseChantier'] ?? ''),
        'numSite' => trim($_POST['numSite'] ?? ''),
        'numContrat' => trim($_POST['numContrat'] ?? ''),
        'idObjet' => !empty($_POST['idObjet']) ? (int) $_POST['idObjet'] : null,
        'idTypeClient' => !empty($_POST['idTypeClient']) ? (int) $_POST['idTypeClient'] : null,
        'idMarque' => !empty($_POST['idMarque']) ? (int) $_POST['idMarque'] : null,
        'idFamille' => !empty($_POST['idFamille']) ? (int) $_POST['idFamille'] : null,
        'commentaire' => trim($_POST['commentaire']),
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

    if (createTicket($data)) {
        header('Location: index.php?p=home');
        exit;
    }

    $error = "Erreur lors de la création du ticket.";
    require('views/tickets/addTicketView.php');
}