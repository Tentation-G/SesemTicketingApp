<?php
require_once('dbModel.php');

/* ============================================================
    AUTHENTIFICATION
   ============================================================ */

function getUserByLogin(string $login): array
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare(
        "SELECT ID AS id, Login AS login, Pass AS pass,
                Nom AS nom, Prenom AS prenom,
                IDRole AS role, IDService AS service, IDEquipe AS equipe
        FROM users WHERE Login = :login AND Actif = 1 LIMIT 1"
    );
    $stmt->execute([':login' => $login]);
    return $stmt->fetch() ?: [];
}

/* ============================================================
    LISTE (admin)
   ============================================================ */

function getAllUsers(array $filters = [], int $page = 1, int $perPage = 25): array
{
    $pdo    = dbConnect();
    $params = [];
    $where  = [];

    if (!empty($filters['search'])) {
        $where[]           = "(u.Nom LIKE :s OR u.Prenom LIKE :s OR u.Login LIKE :s OR u.Email LIKE :s)";
        $params[':s']       = '%' . $filters['search'] . '%';
    }
    if (isset($filters['role']) && $filters['role'] !== '') {
        $where[]            = 'u.IDRole = :role';
        $params[':role']     = (int)$filters['role'];
    }
    if (isset($filters['actif']) && $filters['actif'] !== '') {
        $where[]            = 'u.Actif = :actif';
        $params[':actif']    = (int)$filters['actif'];
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u $whereClause");
    $countStmt->execute($params);
    $total  = (int)$countStmt->fetchColumn();
    $offset = max(0, ($page - 1) * $perPage);

    $sql  = "SELECT
                u.ID, u.Login, u.Nom, u.Prenom, u.Email,
                u.IDRole, u.Actif,
                u.DateInscription, u.DateDerniereConnexion,
                e.Equipe  AS equipeLabel,
                s.Service AS serviceLabel
            FROM users u
            LEFT JOIN equipes  e ON e.IDEquipe  = u.IDEquipe
            LEFT JOIN services s ON s.IDService = u.IDService
            $whereClause
            ORDER BY u.Nom ASC, u.Prenom ASC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();

    return ['users' => $stmt->fetchAll(), 'total' => $total];
}

/* ============================================================
    LECTURE
   ============================================================ */

function getUserById(int $id): array|false
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare(
        "SELECT u.*, e.Equipe AS equipeLabel, s.Service AS serviceLabel
        FROM users u
        LEFT JOIN equipes  e ON e.IDEquipe  = u.IDEquipe
        LEFT JOIN services s ON s.IDService = u.IDService
        WHERE u.ID = :id LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: false;
}

function getEquipes(): array
{
    $pdo = dbConnect();
    return $pdo->query("SELECT IDEquipe AS id, Equipe AS label FROM equipes ORDER BY Equipe ASC")->fetchAll();
}

function getServices(): array
{
    $pdo = dbConnect();
    return $pdo->query("SELECT IDService AS id, Service AS label FROM services ORDER BY Service ASC")->fetchAll();
}

function getRoleOptions(): array
{
    return [1 => 'Administrateur', 2 => 'Manager', 3 => 'Collaborateur'];
}

/* ============================================================
    CRÉATION
   ============================================================ */

function createUser(array $data): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare(
        "INSERT INTO users (Login, Pass, Nom, Prenom, Email, IDRole, IDService, IDEquipe,
                            DateInscription, DateDerniereConnexion, Actif)
        VALUES (:login, :pass, :nom, :prenom, :email, :role, :service, :equipe,
                NOW(), NOW(), :actif)"
    );
    return $stmt->execute([
        ':login'   => trim($data['login']),
        ':pass'    => password_hash($data['password'], PASSWORD_DEFAULT),
        ':nom'     => trim($data['nom']),
        ':prenom'  => trim($data['prenom']),
        ':email'   => trim($data['email']),
        ':role'    => (int)$data['role'],
        ':service' => !empty($data['service']) ? (int)$data['service'] : null,
        ':equipe'  => !empty($data['equipe'])  ? (int)$data['equipe']  : null,
        ':actif'   => isset($data['actif']) ? 1 : 0,
    ]);
}

/* ============================================================
    MISE À JOUR
   ============================================================ */

function updateUser(int $id, array $data): bool
{
    $pdo    = dbConnect();
    $fields = [
        'Nom      = :nom',
        'Prenom   = :prenom',
        'Email    = :email',
        'IDRole   = :role',
        'IDService = :service',
        'IDEquipe  = :equipe',
        'Actif    = :actif',
    ];
    $params = [
        ':id'      => $id,
        ':nom'     => trim($data['nom']),
        ':prenom'  => trim($data['prenom']),
        ':email'   => trim($data['email']),
        ':role'    => (int)$data['role'],
        ':service' => !empty($data['service']) ? (int)$data['service'] : null,
        ':equipe'  => !empty($data['equipe'])  ? (int)$data['equipe']  : null,
        ':actif'   => isset($data['actif']) ? 1 : 0,
    ];

    // Changer le mot de passe seulement si fourni
    if (!empty($data['password'])) {
        $fields[]          = 'Pass = :pass';
        $params[':pass']    = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $sql  = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE ID = :id';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/* ============================================================
    SUPPRESSION / TOGGLE
   ============================================================ */

function toggleUserActif(int $id): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare("UPDATE users SET Actif = IF(Actif = 1, 0, 1) WHERE ID = :id");
    return $stmt->execute([':id' => $id]);
}

function deleteUser(int $id): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare("DELETE FROM users WHERE ID = :id");
    return $stmt->execute([':id' => $id]);
}

/* ============================================================
    LOGIN UNIQUE
   ============================================================ */

function loginExists(string $login, int $excludeId = 0): bool
{
    $pdo  = dbConnect();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Login = :login AND ID != :id");
    $stmt->execute([':login' => $login, ':id' => $excludeId]);
    return (int)$stmt->fetchColumn() > 0;
}