<?php
require_once('dbModel.php');

/* ============================================================
    UTILS / OPTIONS
   ============================================================ */

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
    if (!in_array($table, $allowedTables, true)) return [];

    $pdo  = dbConnect();
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :column");
    $stmt->execute([':column' => $column]);
    $result = $stmt->fetch();

    if (!$result || empty($result['Type'])) return [];
    preg_match("/^enum\((.*)\)$/", $result['Type'], $matches);
    if (empty($matches[1])) return [];

    return str_getcsv($matches[1], ',', "'", "\\");
}

function getPrioriteOptions(): array
{
    return [
        1 => 'Très basse',
        2 => 'Basse',
        3 => 'Moyenne',
        4 => 'Haute',
        5 => 'Urgente',
    ];
}

function getStatutOptions(): array
{
    return [
        1 => ['label' => 'Ouvert',     'color' => '#4f8ef7'],
        2 => ['label' => 'En cours',   'color' => '#f59e0b'],
        3 => ['label' => 'En attente', 'color' => '#8b5cf6'],
        4 => ['label' => 'Résolu',     'color' => '#10b981'],
        5 => ['label' => 'Clôturé',    'color' => '#6b7280'],
    ];
}

function getObjets(): array
{
    $pdo = dbConnect();
    return $pdo->query("SELECT IDObjet AS id, Objet AS label FROM objets ORDER BY Objet ASC")->fetchAll();
}

function getMarques(): array
{
    $pdo = dbConnect();
    return $pdo->query("SELECT IDMarque AS id, Marque AS label FROM marques ORDER BY Marque ASC")->fetchAll();
}

function getFamilles(): array
{
    $pdo = dbConnect();
    return $pdo->query("SELECT IDFamille AS id, Famille AS label, IDMarque AS idMarque FROM familles ORDER BY Famille ASC")->fetchAll();
}

function getTypesClients(): array
{
    $pdo = dbConnect();
    return $pdo->query("SELECT IDTypeClient AS id, TypeClient AS label FROM types_clients ORDER BY TypeClient ASC")->fetchAll();
}

/* ============================================================
    LISTE DES TICKETS
   ============================================================ */

function getTickets(int $role, int $idUser, ?int $idEquipe, array $filters = [], int $page = 1, int $perPage = 20): array
{
    $pdo    = dbConnect();
    $params = [];
    $where  = [];

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

    if (!empty($filters['statut']))   { $where[] = 't.Statut = :statut';     $params[':statut']   = (int)$filters['statut']; }
    if (!empty($filters['type']))     { $where[] = 't.Type = :type';         $params[':type']     = $filters['type']; }
    if (!empty($filters['priorite'])) { $where[] = 't.Priorite = :priorite'; $params[':priorite'] = (int)$filters['priorite']; }
    if (!empty($filters['search']))   {
        $where[]           = '(t.NomClient LIKE :search OR t.IDTicket LIKE :search)';
        $params[':search']  = '%' . $filters['search'] . '%';
    }

    $whereClause  = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $allowedSorts = ['IDTicket', 'Type', 'Priorite', 'Statut', 'NomClient', 'DateInsert'];
    $sortCol      = in_array($filters['sort'] ?? '', $allowedSorts, true) ? $filters['sort'] : 'IDTicket';
    $sortDir      = ($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tickets t $whereClause");
    $countStmt->execute($params);
    $total  = (int)$countStmt->fetchColumn();
    $offset = max(0, ($page - 1) * $perPage);

    $sql  = "SELECT t.IDTicket, t.Type, t.Priorite, t.Statut, t.NomClient, t.DateInsert,
                    CONCAT(u.Prenom, ' ', u.Nom) AS technicien
            FROM tickets t
            LEFT JOIN users u ON u.ID = t.IDUser
            $whereClause
            ORDER BY t.$sortCol $sortDir
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();

    return ['tickets' => $stmt->fetchAll(), 'total' => $total];
}

/* ============================================================
    DÉTAIL D'UN TICKET
   ============================================================ */

function getTicketById(int $id): array|false
{
    $pdo  = dbConnect();
    $sql  = "SELECT
                t.*,
                CONCAT(u.Prenom, ' ', u.Nom)    AS technicienNom,
                u.ID                            AS technicienId,
                ob.Objet                        AS objetLabel,
                ma.Marque                       AS marqueLabel,
                fa.Famille                      AS familleLabel,
                tc.TypeClient                   AS typeClientLabel,
                eq.Equipe                       AS equipeLabel,
                sv.Service                      AS serviceLabel
            FROM tickets t
            LEFT JOIN users          u  ON u.ID              = t.IDUser
            LEFT JOIN objets         ob ON ob.IDObjet        = t.IDObjet
            LEFT JOIN marques        ma ON ma.IDMarque       = t.IDMarque
            LEFT JOIN familles       fa ON fa.IDFamille      = t.IDFamille
            LEFT JOIN types_clients  tc ON tc.IDTypeClient   = t.IDTypeClient
            LEFT JOIN equipes        eq ON eq.IDEquipe       = t.IDEquipe
            LEFT JOIN services       sv ON sv.IDService      = t.IDService
            WHERE t.IDTicket = :id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: false;
}

function getTicketCommentaires(int $idTicket): array
{
    $pdo  = dbConnect();
    $sql  = "SELECT tc.*, CONCAT(u.Prenom, ' ', u.Nom) AS auteurNom,
                    u.IDRole AS auteurRole
            FROM ticket_commentaires tc
            JOIN users u ON u.ID = tc.IDUser
            WHERE tc.IDTicket = :id
            ORDER BY tc.DateInsert ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idTicket]);
    return $stmt->fetchAll();
}

function getTechniciens(): array
{
    $pdo = dbConnect();
    $sql = "SELECT ID AS id, CONCAT(Prenom, ' ', Nom) AS label, IDRole AS role
            FROM users WHERE Actif = 1 ORDER BY Nom ASC";
    return $pdo->query($sql)->fetchAll();
}

/* ============================================================
    ACTIONS SUR UN TICKET
   ============================================================ */

function updateTicketStatut(int $idTicket, int $statut): bool
{
    $pdo  = dbConnect();
    $dateCloture = $statut === 5 ? 'NOW()' : 'NULL';
    $stmt = $pdo->prepare("UPDATE tickets SET Statut = :statut, DateUpdate = NOW(), DateCloture = $dateCloture WHERE IDTicket = :id");
    return $stmt->execute([':statut' => $statut, ':id' => $idTicket]);
}

function assignTicket(int $idTicket, ?int $idUser): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare("UPDATE tickets SET IDUser = :idUser, DateUpdate = NOW() WHERE IDTicket = :id");
    return $stmt->execute([':idUser' => $idUser, ':id' => $idTicket]);
}

function addCommentaire(int $idTicket, int $idUser, string $contenu, bool $isNote): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare(
        "INSERT INTO ticket_commentaires (IDTicket, IDUser, Contenu, IsNote, DateInsert)
        VALUES (:idTicket, :idUser, :contenu, :isNote, NOW())"
    );
    return $stmt->execute([
        ':idTicket' => $idTicket,
        ':idUser'   => $idUser,
        ':contenu'  => trim($contenu),
        ':isNote'   => $isNote ? 1 : 0,
    ]);
}

function cloturerTicket(int $idTicket): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare("UPDATE tickets SET Statut = 5, DateCloture = NOW(), DateUpdate = NOW() WHERE IDTicket = :id");
    return $stmt->execute([':id' => $idTicket]);
}

function deleteTicket(int $idTicket): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare("DELETE FROM tickets WHERE IDTicket = :id");
    return $stmt->execute([':id' => $idTicket]);
}

/* ============================================================
    CRÉATION DE TICKET
   ============================================================ */

function createTicket(array $data): bool
{
    $pdo  = dbConnect();
    $sql  = "INSERT INTO tickets (
                Type, IDUser, IDService, IDEquipe, Priorite,
                DateInsert, DateUpdate, Statut,
                NomClient, Adresse, CodePostal, Ville,
                AdresseChantier, NumSite, NumContrat,
                IDObjet, IDTypeClient, IDMarque, IDFamille,
                Commentaire, TempsTraitement, DateCloture
            ) VALUES (
                :type, :idUser, :idService, :idEquipe, :priorite,
                NOW(), NOW(), 1,
                :nomClient, :adresse, :codePostal, :ville,
                :adresseChantier, :numSite, :numContrat,
                :idObjet, :idTypeClient, :idMarque, :idFamille,
                :commentaire, 0, NULL
            )";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':type'            => $data['type'],
        ':idUser'          => $data['idUser'],
        ':idService'       => $data['idService'],
        ':idEquipe'        => $data['idEquipe'],
        ':priorite'        => $data['priorite'],
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
    ]);
}


function editCommentaire(int $idCommentaire, int $idUser, string $contenu): bool
{
    $pdo  = dbConnect();
    // Sécurité : on vérifie que c'est bien l'auteur qui modifie
    $stmt = $pdo->prepare(
        "UPDATE ticket_commentaires
        SET Contenu = :contenu
        WHERE IDCommentaire = :id AND IDUser = :idUser"
    );
    return $stmt->execute([
        ':contenu' => trim($contenu),
        ':id'      => $idCommentaire,
        ':idUser'  => $idUser,
    ]);
}