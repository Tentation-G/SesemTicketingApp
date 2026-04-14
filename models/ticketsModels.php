<?php
require_once('dbModel.php');

/* == UTILS == */

function loadTicketFormOptions(): array
{
    return [
        'typeOptions'       => getEnumValues('tickets', 'Type'),
        'prioriteOptions'   => getPrioriteOptions(),
        'objetOptions'      => getObjets(),
        'marqueOptions'     => getMarques(),
        'familleOptions'    => getFamilles(),
        'typeClientOptions' => getTypesClients()
    ];
}

function getEnumValues(string $table, string $column): array
{
    $allowedTables = ['tickets'];
    if (!in_array($table, $allowedTables, true)) {
        return [];
    }

    $pdo  = dbConnect();
    $sql  = "SHOW COLUMNS FROM `$table` LIKE :column";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':column' => $column]);

    $result = $stmt->fetch();

    if (!$result || empty($result['Type'])) {
        return [];
    }

    preg_match("/^enum\((.*)\)$/", $result['Type'], $matches);

    if (empty($matches[1])) {
        return [];
    }

    return str_getcsv($matches[1], ',', "'", "\\");
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

/**
 * Mapping statuts : entier → libellé + couleur badge CSS.
 * Ajouter un statut = ajouter une entrée ici, rien d'autre à toucher.
 */
function getStatutOptions(): array
{
    return [
        1 => ['label' => 'Ouvert',      'color' => '#2196F3'],
        2 => ['label' => 'En cours',    'color' => '#FF9800'],
        3 => ['label' => 'En attente',  'color' => '#9C27B0'],
        4 => ['label' => 'Résolu',      'color' => '#4CAF50'],
        5 => ['label' => 'Clôturé',     'color' => '#9E9E9E'],
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

function createTicket(array $data): bool
{
    $pdo = dbConnect();

    $sql = "INSERT INTO tickets (
                Type, IDUser, IDService, IDEquipe, Priorite,
                DateInsert, DateUpdate, Statut,
                NomClient, Adresse, CodePostal, Ville,
                AdresseChantier, NumSite, NumContrat,
                IDObjet, IDTypeClient, IDMarque, IDFamille,
                Commentaire, TempsTraitement, DateCloture
            ) VALUES (
                :type, :idUser, :idService, :idEquipe, :priorite,
                NOW(), NOW(), :statut,
                :nomClient, :adresse, :codePostal, :ville,
                :adresseChantier, :numSite, :numContrat,
                :idObjet, :idTypeClient, :idMarque, :idFamille,
                :commentaire, :tempsTraitement, NULL
            )";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':type'            => $data['type'],
        ':idUser'          => $data['idUser'],
        ':idService'       => $data['idService'],
        ':idEquipe'        => $data['idEquipe'],
        ':priorite'        => $data['priorite'],
        ':statut'          => 1,
        ':nomClient'       => $data['nomClient'],
        ':adresse'         => $data['adresse'],
        ':codePostal'      => $data['codePostal'],
        ':ville'           => $data['ville'],
        ':adresseChantier' => $data['adresseChantier'],
        ':numSite'         => $data['numSite'],
        ':numContrat'      => $data['numContrat'],
        ':idObjet'         => $data['idObjet'],
        ':idTypeClient'    => $data['idTypeClient'],
        ':idMarque'        => $data['idMarque'],
        ':idFamille'       => $data['idFamille'],
        ':commentaire'     => $data['commentaire'],
        ':tempsTraitement' => 0
    ]);
}

/**
 * Liste les tickets selon le rôle :
 *   1 = Admin      → tous les tickets
 *   2 = Manager    → tickets de son équipe
 *   3 = Collaborateur → ses tickets uniquement
 *
 * Filtres supportés : statut, type, priorite, search (nomClient ou IDTicket)
 * Tri supporté      : IDTicket, Type, Priorite, Statut, NomClient, DateInsert
 *
 * @return array ['tickets' => array, 'total' => int]
 */
function getTickets(
    int $role,
    int $idUser,
    ?int $idEquipe,
    array $filters = [],
    int $page    = 1,
    int $perPage = 20
): array {
    $pdo    = dbConnect();
    $params = [];
    $where  = [];

    // Restriction par rôle
    if ($role === 3) {
        $where[]           = 't.IDUser = :idUser';
        $params[':idUser']  = $idUser;
    } elseif ($role === 2) {
        if ($idEquipe) {
            $where[]             = 't.IDEquipe = :idEquipe';
            $params[':idEquipe']  = $idEquipe;
        } else {
            $where[]           = 't.IDUser = :idUser';
            $params[':idUser']  = $idUser;
        }
    }
    // Rôle 1 : aucune restriction

    // Filtres
    if (!empty($filters['statut'])) {
        $where[]            = 't.Statut = :statut';
        $params[':statut']   = (int) $filters['statut'];
    }
    if (!empty($filters['type'])) {
        $where[]          = 't.Type = :type';
        $params[':type']   = $filters['type'];
    }
    if (!empty($filters['priorite'])) {
        $where[]               = 't.Priorite = :priorite';
        $params[':priorite']    = (int) $filters['priorite'];
    }
    if (!empty($filters['search'])) {
        $where[]             = '(t.NomClient LIKE :search OR t.IDTicket LIKE :search)';
        $params[':search']    = '%' . $filters['search'] . '%';
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Tri
    $allowedSorts = ['IDTicket', 'Type', 'Priorite', 'Statut', 'NomClient', 'DateInsert'];
    $sortCol      = in_array($filters['sort'] ?? '', $allowedSorts, true)
                        ? $filters['sort']
                        : 'IDTicket';
    $sortDir      = ($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    // Total pour pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tickets t $whereClause");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // Requête principale
    $offset = max(0, ($page - 1) * $perPage);

    $sql = "SELECT
                t.IDTicket,
                t.Type,
                t.Priorite,
                t.Statut,
                t.NomClient,
                t.DateInsert,
                CONCAT(u.Prenom, ' ', u.Nom) AS technicien
            FROM tickets t
            LEFT JOIN users u ON u.ID = t.IDUser
            $whereClause
            ORDER BY t.$sortCol $sortDir
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();

    return [
        'tickets' => $stmt->fetchAll(),
        'total'   => $total
    ];
}