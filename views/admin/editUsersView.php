<?php ob_start();

$errorMsgs = [
    'required' => 'Tous les champs obligatoires doivent être remplis.',
    'server'   => 'Une erreur serveur est survenue. Consulte les logs.',
];
$successMsgs = [
    'saved'   => 'Modifications enregistrées.',
    'toggled' => 'Statut modifié.',
];

// Si on revient après une erreur POST, on pré-remplit depuis $formData
// Sinon on utilise les données du $user venant de la base
$val = function(string $key) use ($formData, $user): string {
    return htmlspecialchars((string)($formData[$key] ?? $user[$key] ?? ''));
};

$currentRole    = (int)($formData['role']    ?? $user['IDRole']    ?? 3);
$currentEquipe  = (int)($formData['equipe']  ?? $user['IDEquipe']  ?? 0);
$currentService = (int)($formData['service'] ?? $user['IDService'] ?? 0);
$currentActif   = isset($formData['actif'])
    ? (bool)$formData['actif']
    : (bool)$user['Actif'];

$isSelf = (int)$user['ID'] === (int)$_SESSION['user']['id'];
?>

<!-- ══ EN-TÊTE ════════════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <a href="index.php?p=usersView" class="detail-back">← Utilisateurs</a>
        </div>
        <h1 class="page-header__title">
            <?= htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']) ?>
        </h1>
        <p class="page-header__sub">Login : <code style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--c-blue)"><?= htmlspecialchars($user['Login']) ?></code></p>
    </div>

    <?php if (!$isSelf) : ?>
    <div style="display:flex;gap:8px;flex-shrink:0">
        <!-- Toggle actif/inactif -->
        <form method="post" action="index.php?p=toggleUserAction">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id"         value="<?= (int)$user['ID'] ?>">
            <input type="hidden" name="redirect"   value="index.php?p=editUserView&id=<?= (int)$user['ID'] ?>">
            <button type="submit" class="btn btn--ghost btn--sm"
                    onclick="return confirm('<?= $user['Actif'] ? 'Désactiver' : 'Activer' ?> ce compte ?')">
                <?= $user['Actif'] ? '⏸ Désactiver' : '▶ Activer' ?>
            </button>
        </form>

        <!-- Suppression -->
        <form method="post" action="index.php?p=deleteUserAction">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id"         value="<?= (int)$user['ID'] ?>">
            <button type="submit" class="btn btn--danger btn--sm"
                    onclick="return confirm('Supprimer définitivement ce compte ? Cette action est irréversible.')">
                Supprimer
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<!-- Bannières -->
<?php if (!empty($success) && isset($successMsgs[$success])) : ?>
<div class="alert alert--success" style="margin-bottom:20px">
    <span class="alert__icon">✓</span> <?= htmlspecialchars($successMsgs[$success]) ?>
</div>
<?php endif; ?>

<?php if (!empty($error) && isset($errorMsgs[$error])) : ?>
<div class="alert alert--error" style="margin-bottom:20px">
    <span class="alert__icon">⚠</span> <?= htmlspecialchars($errorMsgs[$error]) ?>
</div>
<?php endif; ?>

<!-- ══ FORMULAIRE ÉDITION ════════════════════════════════════ -->
<form method="post" action="index.php?p=updateUserAction" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="id"         value="<?= (int)$user['ID'] ?>">

    <div class="form-card">
        <div class="form-section"><div class="form-section__title">Identité</div></div>
        <div class="form-grid">
            <div class="form-group">
                <label for="e_prenom">Prénom <span class="required">*</span></label>
                <input type="text" id="e_prenom" name="prenom" class="form-control"
                        value="<?= $val('Prenom') ?>" required>
            </div>
            <div class="form-group">
                <label for="e_nom">Nom <span class="required">*</span></label>
                <input type="text" id="e_nom" name="nom" class="form-control"
                        value="<?= $val('Nom') ?>" required>
            </div>
            <div class="form-group form-col-full">
                <label for="e_email">Email <span class="required">*</span></label>
                <input type="email" id="e_email" name="email" class="form-control"
                        value="<?= $val('Email') ?>" required>
            </div>
            <div class="form-group form-col-full">
                <label for="e_password">
                    Nouveau mot de passe
                    <span class="label-hint">Laisser vide pour ne pas modifier</span>
                </label>
                <input type="password" id="e_password" name="password" class="form-control"
                        placeholder="••••••••" autocomplete="new-password">
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-section"><div class="form-section__title">Accès & affectation</div></div>
        <div class="form-grid form-grid--3col">
            <div class="form-group">
                <label for="e_role">Rôle <span class="required">*</span></label>
                <select id="e_role" name="role" class="form-control" required
                        <?= $isSelf ? 'disabled' : '' ?>>
                    <?php foreach ($roleOptions as $rid => $rlabel) : ?>
                    <option value="<?= $rid ?>" <?= $currentRole === $rid ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rlabel) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isSelf) : ?>
                    <!-- Champ caché car disabled n'est pas soumis -->
                    <input type="hidden" name="role" value="<?= $currentRole ?>">
                    <span class="label-hint" style="margin-top:4px;display:block">Vous ne pouvez pas modifier votre propre rôle.</span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="e_equipe">Équipe</label>
                <select id="e_equipe" name="equipe" class="form-control">
                    <option value="">— Aucune —</option>
                    <?php foreach ($equipes as $eq) : ?>
                    <option value="<?= (int)$eq['id'] ?>" <?= $currentEquipe === (int)$eq['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($eq['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="e_service">Service</label>
                <select id="e_service" name="service" class="form-control">
                    <option value="">— Aucun —</option>
                    <?php foreach ($services as $sv) : ?>
                    <option value="<?= (int)$sv['id'] ?>" <?= $currentService === (int)$sv['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sv['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!$isSelf) : ?>
        <div class="form-group" style="margin-top:6px">
            <label class="toggle-label">
                <input type="checkbox" name="actif" value="1"
                        class="toggle-input"
                        <?= $currentActif ? 'checked' : '' ?>>
                <span class="toggle-track"></span>
                <span style="font-size:13px;color:var(--c-secondary);text-transform:none;letter-spacing:0;font-weight:400">
                    Compte actif
                </span>
            </label>
        </div>
        <?php else : ?>
            <!-- Un admin ne peut pas se désactiver lui-même -->
            <input type="hidden" name="actif" value="1">
        <?php endif; ?>
    </div>

    <!-- Infos en lecture seule -->
    <div class="form-card">
        <div class="form-section"><div class="form-section__title">Informations système</div></div>
        <div class="detail-fields">
            <div class="detail-field">
                <span class="detail-field__label">Date d'inscription</span>
                <span class="detail-field__value">
                    <?= $user['DateInscription']
                        ? (new DateTime($user['DateInscription']))->format('d/m/Y H:i')
                        : '—' ?>
                </span>
            </div>
            <div class="detail-field">
                <span class="detail-field__label">Dernière connexion</span>
                <span class="detail-field__value">
                    <?= $user['DateDerniereConnexion']
                        ? (new DateTime($user['DateDerniereConnexion']))->format('d/m/Y H:i')
                        : '—' ?>
                </span>
            </div>
            <div class="detail-field">
                <span class="detail-field__label">Équipe actuelle</span>
                <span class="detail-field__value"><?= htmlspecialchars($user['equipeLabel']  ?? '—') ?></span>
            </div>
            <div class="detail-field">
                <span class="detail-field__label">Service actuel</span>
                <span class="detail-field__value"><?= htmlspecialchars($user['serviceLabel'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <span class="form-actions__hint"><span>*</span> Champs obligatoires</span>
        <a href="index.php?p=usersView" class="btn btn--ghost">Retour à la liste</a>
        <button type="submit" class="btn btn--primary">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16.5 3.5l-9 9-4-4"/>
            </svg>
            Enregistrer
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
$title   = 'Modifier — ' . $user['Prenom'] . ' ' . $user['Nom'];
require_once('views/layout/baseLayout.php');
?>