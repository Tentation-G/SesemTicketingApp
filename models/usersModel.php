<?php

require('dbModel.php');

function getUserByLogin(string $login) {
    $pdo = dbConnect();

    $sql = "SELECT 
                ID AS id,
                Login AS login,
                Pass AS pass,
                Nom AS nom,
                Prenom AS prenom,
                IDRole AS role,
                IDService AS service,
                IDEquipe AS equipe
            FROM users
            WHERE Login = :login
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':login' => $login
    ]);

    $user = $stmt->fetch();

    return $user ?: [];
}

function createUser(string $login, string $password, string $nom, string $prenom, string $email): bool {
    $pdo = dbConnect();

    $sql = "INSERT INTO users (
                Login,
                Pass,
                IDRole,
                Nom,
                Prenom,
                Email,
                DateInscription,
                DateDerniereConnexion,
                Actif
            ) VALUES (
                :login,
                :pass,
                :role,
                :nom,
                :prenom,
                :email,
                NOW(),
                NOW(),
                1
            )";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':login' => $login,
        ':pass' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => 2,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email
    ]);
}