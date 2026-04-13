<?php 

function dbConnect() {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=mvc_isesemtickets', // serveur + nom bdd
        'root',  // identifiant
        'root', // mot de passe
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,         // erreur SQL
            Pdo\Mysql::ATTR_INIT_COMMAND => 'SET NAMES utf8', // encodage utf8
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Fetch par défaut
        ] // tableau des options
    );

    return $pdo;
}

?>