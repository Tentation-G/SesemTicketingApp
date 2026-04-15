<?php ob_start();

function buildUserUrl(array $overrides = []): string {
    $params = array_merge($_GET, $overrides);
    return 'index.php?' . http_build_query($params);
}

function initiales(string $name): string {
    $p = array_filter(explode(' ', trim($name)));
    return count($p) >= 2
        ? strtoupper(mb_substr(reset($p), 0, 1) . mb_substr(end($p), 0, 1))
        : strtoupper(mb_substr($name, 0, 2));
}

$roleLabels = [1 => 'Admin', 2 => 'Manager', 3 => 'Collaborateur'];
$roleColors = [1 => '#ef4444', 2 => '#f59e0b', 3 => '#4f8ef7'];

$errorMsgs = [
    'required'   => 'Tous les champs obligatoires doivent être remplis.',
    'login_taken'=> 'Ce login est déjà utilisé par un autre compte.',
    'server'     => 'Une erreur serveur est survenue. Consulte les logs.',
];
$successMsgs = [
    'created' => 'Utilisateur créé avec succès.',
    'deleted' => 'Utilisateur supprimé.',
    'toggled' => 'Statut modifié.',
];
?>

<!-- ══ EN-TÊTE ════════════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <h1 class="page-header__title">Utilisateurs</h1>
        <p class="page-header__sub"><?= $total ?> compte<?= $total > 1 ? 's' : '' ?> enregistré<?= $total > 1 ? 's' : '' ?></p>
    </div>
    <a href="#form-create" class="btn btn--primary">
        <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10 4v12M4 10h12"/>
        </svg>
        Nouvel utilisateur
    </a>
</div>

<!-- Bannières retour -->
<?php if (!empty($success) && isset($successMsgs[$success])) : ?>
<div class="alert alert--success">
    <span class="alert__icon">✓</span>
    <?= htmlspecialchars($successMsgs[$success]) ?>
</div>
<?php endif; ?>

<?php if (!empty($error) && isset($errorMsgs[$error]) && empty($createData)) : ?>
<div class="alert alert--error">
    <span class="alert__icon">⚠</span>
    <?= htmlspecialchars($errorMsgs[$error]) ?>
</div>
<?php endif; ?>

<!-- ══ FILTRES ═════════════════════════════════════════════════ -->
<form method="GET" action="index.php" class="tickets-filters">
    <input type="hidden" name="p" value="usersView">

    <label class="filter-input">
        <svg class="filter-icon" width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="9" cy="9" r="6"/><path d="M15 15l3 3"/>
        </svg>
        <input type="text" name="search"
                placeholder="Nom, login, email…"
                value="<?= htmlspecialchars($filters['search']) ?>">
    </label>

    <label class="filter-input">
        <select name="role">
            <option value="">Tous les rôles</option>
            <?php foreach ($roleOptions as $rid => $rlabel) : ?>
            <option value="<?= $rid ?>" <?= ((string)($filters['role'] ?? '') === (string)$rid) ? 'selected' : '' ?>>
                <?= htmlspecialchars($rlabel) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label class="filter-input">
        <select name="actif">
            <option value="">Tous les statuts</option>
            <option value="1" <?= ($filters['actif'] === '1') ? 'selected' : '' ?>>Actifs</option>
            <option value="0" <?= ($filters['actif'] === '0') ? 'selected' : '' ?>>Inactifs</option>
        </select>
    </label>

    <button type="submit" class="btn btn--primary btn--sm">Filtrer</button>
    <a href="index.php?p=usersView" class="btn btn--ghost btn--sm">Réinitialiser</a>
</form>

<!-- ══ TABLEAU ═════════════════════════════════════════════════ -->
<div class="card" style="margin-bottom:32px">
    <div class="table-wrapper">
        <table class="tickets-table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Login</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Équipe</th>
                    <th>Service</th>
                    <th>Dernière connexion</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)) : ?>
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-state__icon">👤</div>
                            <div class="empty-state__title">Aucun utilisateur trouvé</div>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($users as $u) :
                    $init      = initiales($u['Prenom'] . ' ' . $u['Nom']);
                    $roleId    = (int)$u['IDRole'];
                    $roleLbl   = $roleLabels[$roleId]  ?? '?';
                    $roleColor = $roleColors[$roleId]  ?? '#888';
                    $isActive  = (bool)$u['Actif'];
                    $isSelf    = (int)$u['ID'] === (int)$_SESSION['user']['id'];
                    $lastCo    = $u['DateDerniereConnexion']
                        ? (new DateTime($u['DateDerniereConnexion']))->format('d/m/Y H:i')
                        : '—';
                ?>
                <tr class="<?= !$isActive ? 'user-row--inactive' : '' ?>">
                    <td>
                        <div class="col-tech">
                            <div class="col-tech-avatar"
                                style="background:linear-gradient(135deg,<?= $roleColor ?>99,<?= $roleColor ?>44)">
                                <?= $init ?>
                            </div>
                            <div>
                                <div style="font-weight:500;font-size:13px">
                                    <?= htmlspecialchars($u['Prenom'] . ' ' . $u['Nom']) ?>
                                </div>
                                <?php if ($isSelf) : ?>
                                <div style="font-size:10.5px;color:var(--c-blue)">Vous</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><span class="col-id"><?= htmlspecialchars($u['Login']) ?></span></td>
                    <td class="col-date"><?= htmlspecialchars($u['Email'] ?? '—') ?></td>
                    <td>
                        <span class="badge"
                            style="color:<?= $roleColor ?>;background:<?= $roleColor ?>18;border:1px solid <?= $roleColor ?>33">
                            <?= htmlspecialchars($roleLbl) ?>
                        </span>
                    </td>
                    <td class="col-date"><?= htmlspecialchars($u['equipeLabel']  ?? '—') ?></td>
                    <td class="col-date"><?= htmlspecialchars($u['serviceLabel'] ?? '—') ?></td>
                    <td class="col-date"><?= $lastCo ?></td>
                    <td>
                        <?php if ($isActive) : ?>
                            <span class="badge badge--resolved">Actif</span>
                        <?php else : ?>
                            <span class="badge badge--closed">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:5px">
                            <!-- Bouton modifier → page dédiée -->
                            <a href="index.php?p=editUserView&id=<?= (int)$u['ID'] ?>"
                                class="btn btn--ghost btn--sm row-action" title="Modifier">
                                ✏
                            </a>

                            <?php if (!$isSelf) : ?>
                            <!-- Toggle actif/inactif -->
                            <form method="post" action="index.php?p=toggleUserAction" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id"         value="<?= (int)$u['ID'] ?>">
                                <input type="hidden" name="redirect"   value="index.php?p=usersView">
                                <button type="submit"
                                        class="btn btn--ghost btn--sm btn--icon row-action"
                                        title="<?= $isActive ? 'Désactiver' : 'Activer' ?>"
                                        onclick="return confirm('<?= $isActive ? 'Désactiver' : 'Activer' ?> <?= htmlspecialchars(addslashes($u['Prenom'] . ' ' . $u['Nom'])) ?> ?')">
                                    <?= $isActive ? '⏸' : '▶' ?>
                                </button>
                            </form>

                            <!-- Suppression -->
                            <form method="post" action="index.php?p=deleteUserAction" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id"         value="<?= (int)$u['ID'] ?>">
                                <button type="submit"
                                        class="btn btn--danger btn--sm btn--icon row-action"
                                        title="Supprimer"
                                        onclick="return confirm('Supprimer définitivement <?= htmlspecialchars(addslashes($u['Prenom'] . ' ' . $u['Nom'])) ?> ? Cette action est irréversible.')">
                                    ✕
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1) : ?>
    <div class="pagination">
        <span class="pagination__info">
            <?= (($page - 1) * 25) + 1 ?>–<?= min($page * 25, $total) ?> sur <?= $total ?>
        </span>
        <div class="pagination__pages">
            <?php if ($page > 1) : ?>
                <a href="<?= htmlspecialchars(buildUserUrl(['page' => $page - 1])) ?>" class="pagination__btn pagination__btn--prev">← Préc.</a>
            <?php else : ?>
                <span class="pagination__btn disabled">← Préc.</span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++) :
                if ($i === $page) : ?>
                    <span class="pagination__btn current"><?= $i ?></span>
                <?php elseif ($i === 1 || $i === $totalPages || abs($i - $page) <= 2) : ?>
                    <a href="<?= htmlspecialchars(buildUserUrl(['page' => $i])) ?>" class="pagination__btn"><?= $i ?></a>
                <?php elseif (abs($i - $page) === 3) :
                    echo '<span class="pagination__btn disabled">…</span>';
                endif;
            endfor; ?>

            <?php if ($page < $totalPages) : ?>
                <a href="<?= htmlspecialchars(buildUserUrl(['page' => $page + 1])) ?>" class="pagination__btn pagination__btn--next">Suiv. →</a>
            <?php else : ?>
                <span class="pagination__btn disabled">Suiv. →</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ══ FORMULAIRE CRÉATION ════════════════════════════════════ -->
<div id="form-create">
    <div style="margin-bottom:20px">
        <h2 style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;letter-spacing:-.02em;color:var(--c-primary)">
            Nouvel utilisateur
        </h2>
    </div>

    <?php if (!empty($error) && isset($errorMsgs[$error]) && !empty($createData)) : ?>
    <div class="alert alert--error" style="margin-bottom:20px">
        <span class="alert__icon">⚠</span>
        <?= htmlspecialchars($errorMsgs[$error]) ?>
    </div>
    <?php endif; ?>

    <form method="post" action="index.php?p=createUserAction" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="form-card">
            <div class="form-section"><div class="form-section__title">Identité</div></div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="c_prenom">Prénom <span class="required">*</span></label>
                    <input type="text" id="c_prenom" name="prenom" class="form-control"
                            value="<?= htmlspecialchars($createData['prenom'] ?? '') ?>"
                            placeholder="Marie" required>
                </div>
                <div class="form-group">
                    <label for="c_nom">Nom <span class="required">*</span></label>
                    <input type="text" id="c_nom" name="nom" class="form-control"
                            value="<?= htmlspecialchars($createData['nom'] ?? '') ?>"
                            placeholder="Dupont" required>
                </div>
                <div class="form-group">
                    <label for="c_login">Login <span class="required">*</span></label>
                    <input type="text" id="c_login" name="login" class="form-control"
                            value="<?= htmlspecialchars($createData['login'] ?? '') ?>"
                            placeholder="m.dupont" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="c_password">Mot de passe <span class="required">*</span></label>
                    <input type="password" id="c_password" name="password" class="form-control"
                            placeholder="••••••••" required autocomplete="new-password">
                </div>
                <div class="form-group form-col-full">
                    <label for="c_email">Email <span class="required">*</span></label>
                    <input type="email" id="c_email" name="email" class="form-control"
                            value="<?= htmlspecialchars($createData['email'] ?? '') ?>"
                            placeholder="marie.dupont@sesem.fr" required>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-section"><div class="form-section__title">Accès & affectation</div></div>
            <div class="form-grid form-grid--3col">
                <div class="form-group">
                    <label for="c_role">Rôle <span class="required">*</span></label>
                    <select id="c_role" name="role" class="form-control" required>
                        <?php foreach ($roleOptions as $rid => $rlabel) : ?>
                        <option value="<?= $rid ?>"
                            <?= ((int)($createData['role'] ?? 3) === $rid) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rlabel) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="c_equipe">Équipe</label>
                    <select id="c_equipe" name="equipe" class="form-control">
                        <option value="">— Aucune —</option>
                        <?php foreach ($equipes as $eq) : ?>
                        <option value="<?= (int)$eq['id'] ?>"
                            <?= ((int)($createData['equipe'] ?? 0) === (int)$eq['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="c_service">Service</label>
                    <select id="c_service" name="service" class="form-control">
                        <option value="">— Aucun —</option>
                        <?php foreach ($services as $sv) : ?>
                        <option value="<?= (int)$sv['id'] ?>"
                            <?= ((int)($createData['service'] ?? 0) === (int)$sv['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sv['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:6px">
                <label class="toggle-label">
                    <input type="checkbox" name="actif" value="1"
                            class="toggle-input"
                            <?= !isset($createData['actif']) || $createData['actif'] ? 'checked' : '' ?>>
                    <span class="toggle-track"></span>
                    <span style="font-size:13px;color:var(--c-secondary);text-transform:none;letter-spacing:0;font-weight:400">
                        Compte actif immédiatement
                    </span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <span class="form-actions__hint"><span>*</span> Champs obligatoires</span>
            <a href="index.php?p=usersView" class="btn btn--ghost">Annuler</a>
            <button type="submit" class="btn btn--primary">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16.5 3.5l-9 9-4-4"/>
                </svg>
                Créer l'utilisateur
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$title   = 'Utilisateurs';
require_once('views/layout/baseLayout.php');
?>