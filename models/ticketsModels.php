<?php
require_once('dbModel.php');

/* == UTILS == */

function loadTicketFormOptions(): array
{
    return [
        'typeOptions' => getEnumValues('tickets', 'Type'),
        'prioriteOptions' => getPrioriteOptions(),
        'objetOptions' => getObjets(),
        'marqueOptions' => getMarques(),
        'familleOptions' => getFamilles(),
        'typeClientOptions' => getTypesClients()
    ];
}

function getEnumValues(string $table, string $column): array
{
    $pdo = dbConnect();

    $sql = "SHOW COLUMNS FROM `$table` LIKE :column";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':column' => $column
    ]);

    $result = $stmt->fetch();

    if (!$result || empty($result['Type'])) {
        return [];
    }

    preg_match("/^enum\((.*)\)$/", $result['Type'], $matches);

    if (empty($matches[1])) {
        return [];
    }

    return str_getcsv($matches[1], ',', "'");
}

function getPrioriteOptions(): array
{
    return [
        1 => 'Très basse',
        2 => 'Basse',
        3 => 'Moyenne',
        4 => 'Haute',
        5 => 'Urgente'
    ];
}

function getObjets(): array
{
    $pdo = dbConnect();
    $sql = "SELECT IDObjet AS id, Objet AS label FROM objets ORDER BY Objet ASC";
    return $pdo->query($sql)->fetchAll();
}

function getMarques(): array
{
    $pdo = dbConnect();
    $sql = "SELECT IDMarque AS id, Marque AS label FROM marques ORDER BY Marque ASC";
    return $pdo->query($sql)->fetchAll();
}

function getFamilles(): array
{
    $pdo = dbConnect();
    $sql = "SELECT IDFamille AS id, Famille AS label, IDMarque AS idMarque FROM familles ORDER BY Famille ASC";
    return $pdo->query($sql)->fetchAll();
}

function getTypesClients(): array
{
    $pdo = dbConnect();
    $sql = "SELECT IDTypeClient AS id, TypeClient AS label FROM types_clients ORDER BY TypeClient ASC";
    return $pdo->query($sql)->fetchAll();
}

/* == TICKET == */

function createTicket(array $data): bool {
    $pdo = dbConnect();

    $sql = "INSERT INTO tickets (
                Type,
                IDUser,
                Priorite,
                DateInsert,
                DateUpdate,
                Statut,
                NomClient,
                Adresse,
                CodePostal,
                Ville,
                AdresseChantier,
                NumSite,
                NumContrat,
                IDObjet,
                IDTypeClient,
                IDMarque,
                IDFamille,
                Commentaire,
                TempsTraitement
            ) VALUES (
                :type,
                :idUser,
                :priorite,
                CURRENT_DATE,
                CURRENT_DATE,
                :statut,
                :nomClient,
                :adresse,
                :codePostal,
                :ville,
                :adresseChantier,
                :numSite,
                :numContrat,
                :idObjet,
                :idTypeClient,
                :idMarque,
                :idFamille,
                :commentaire,
                :tempsTraitement
            )";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':type' => $data['type'],
        ':idUser' => $data['idUser'],
        ':priorite' => $data['priorite'],
        ':statut' => 1,
        ':nomClient' => $data['nomClient'],
        ':adresse' => $data['adresse'],
        ':codePostal' => $data['codePostal'],
        ':ville' => $data['ville'],
        ':adresseChantier' => $data['adresseChantier'],
        ':numSite' => $data['numSite'],
        ':numContrat' => $data['numContrat'],
        ':idObjet' => $data['idObjet'],
        ':idTypeClient' => $data['idTypeClient'],
        ':idMarque' => $data['idMarque'],
        ':idFamille' => $data['idFamille'],
        ':commentaire' => $data['commentaire'],
        ':tempsTraitement' => 0
    ]);
}