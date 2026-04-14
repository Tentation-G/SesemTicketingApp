<?php ob_start(); ?>

<!-- ══ EN-TÊTE ════════════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <h1 class="page-header__title">Nouveau <span>ticket</span></h1>
        <p class="page-header__sub">Les champs marqués <span style="color:#4f8ef7">*</span> sont obligatoires.</p>
    </div>
    <a href="index.php?p=listTicketsView" class="btn btn--ghost btn--sm">
        ← Retour aux tickets
    </a>
</div>

<!-- Alerte erreur globale -->
<?php if (!empty($error)) : ?>
<div class="alert alert--error">
    <span class="alert__icon">⚠</span>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form action="index.php?p=addTicket" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <!-- ══ SECTION 1 : Qualification ════════════════════════════ -->
    <div class="form-card">
        <div class="form-section">
            <div class="form-section__title">Qualification</div>
        </div>

        <div class="form-grid">

            <!-- Type -->
            <div class="form-group">
                <label for="type">Type <span class="required">*</span></label>
                <select name="type" id="type" class="form-control <?= !empty($error) && empty($_POST['type']) ? 'has-error' : '' ?>" required>
                    <option value="">— Choisir un type —</option>
                    <?php foreach ($typeOptions as $type) : ?>
                        <option value="<?= htmlspecialchars($type) ?>"
                            <?= (($_POST['type'] ?? '') === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Priorité avec indicateur coloré -->
            <div class="form-group">
                <label for="priorite">Priorité <span class="required">*</span></label>
                <div class="prio-indicator" id="prio-wrapper" data-prio="<?= htmlspecialchars($_POST['priorite'] ?? '') ?>">
                    <select name="priorite" id="priorite"
                            class="form-control <?= !empty($error) && empty($_POST['priorite']) ? 'has-error' : '' ?>"
                            required
                            onchange="setPrioIndicator(this.value)">
                        <option value="">— Choisir une priorité —</option>
                        <?php foreach ($prioriteOptions as $id => $label) : ?>
                            <option value="<?= (int) $id ?>"
                                <?= (isset($_POST['priorite']) && (int) $_POST['priorite'] === $id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

        </div>
    </div>

    <!-- ══ SECTION 2 : Client ════════════════════════════════════ -->
    <div class="form-card">
        <div class="form-section">
            <div class="form-section__title">Informations client</div>
        </div>

        <div class="form-grid">

            <!-- Nom client -->
            <div class="form-group form-col-full">
                <label for="nomClient">Nom client <span class="required">*</span></label>
                <input type="text" name="nomClient" id="nomClient"
                        class="form-control <?= !empty($error) && empty($_POST['nomClient']) ? 'has-error' : '' ?>"
                        placeholder="Société Dupont SA"
                        value="<?= htmlspecialchars($_POST['nomClient'] ?? '') ?>"
                        required>
            </div>

            <!-- Type client -->
            <div class="form-group">
                <label for="idTypeClient">
                    Type client
                    <span class="label-hint">Optionnel</span>
                </label>
                <select name="idTypeClient" id="idTypeClient" class="form-control">
                    <option value="">— Choisir —</option>
                    <?php foreach ($typeClientOptions as $option) : ?>
                        <option value="<?= (int) $option['id'] ?>"
                            <?= (isset($_POST['idTypeClient']) && (int) $_POST['idTypeClient'] === (int) $option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Num contrat -->
            <div class="form-group">
                <label for="numContrat">
                    N° contrat
                    <span class="label-hint">Optionnel</span>
                </label>
                <input type="text" name="numContrat" id="numContrat"
                       class="form-control"
                       placeholder="CTR-2024-001"
                       value="<?= htmlspecialchars($_POST['numContrat'] ?? '') ?>">
            </div>

        </div>
    </div>

    <!-- ══ SECTION 3 : Adresse ═══════════════════════════════════ -->
    <div class="form-card">
        <div class="form-section">
            <div class="form-section__title">Localisation</div>
        </div>

        <div class="form-grid">

            <!-- Adresse -->
            <div class="form-group form-col-full">
                <label for="adresse">Adresse <span class="required">*</span></label>
                <input type="text" name="adresse" id="adresse"
                       class="form-control <?= !empty($error) && empty($_POST['adresse']) ? 'has-error' : '' ?>"
                       placeholder="12 rue de la Paix"
                       value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>"
                       required>
            </div>

            <!-- Code postal -->
            <div class="form-group">
                <label for="codePostal">Code postal <span class="required">*</span></label>
                <input type="text" name="codePostal" id="codePostal"
                       class="form-control <?= !empty($error) && empty($_POST['codePostal']) ? 'has-error' : '' ?>"
                       placeholder="75001"
                       value="<?= htmlspecialchars($_POST['codePostal'] ?? '') ?>"
                       maxlength="10"
                       required>
            </div>

            <!-- Ville -->
            <div class="form-group">
                <label for="ville">Ville <span class="required">*</span></label>
                <input type="text" name="ville" id="ville"
                       class="form-control <?= !empty($error) && empty($_POST['ville']) ? 'has-error' : '' ?>"
                       placeholder="Paris"
                       value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>"
                       required>
            </div>

            <!-- Adresse chantier -->
            <div class="form-group form-col-full">
                <label for="adresseChantier">
                    Adresse chantier
                    <span class="label-hint">Si différente de l'adresse client</span>
                </label>
                <textarea name="adresseChantier" id="adresseChantier"
                          class="form-control"
                          placeholder="Adresse du site d'intervention si différente…"><?= htmlspecialchars($_POST['adresseChantier'] ?? '') ?></textarea>
            </div>

            <!-- Num site -->
            <div class="form-group">
                <label for="numSite">
                    N° site
                    <span class="label-hint">Optionnel</span>
                </label>
                <input type="text" name="numSite" id="numSite"
                       class="form-control"
                       placeholder="SITE-042"
                       value="<?= htmlspecialchars($_POST['numSite'] ?? '') ?>">
            </div>

        </div>
    </div>

    <!-- ══ SECTION 4 : Matériel ══════════════════════════════════ -->
    <div class="form-card">
        <div class="form-section">
            <div class="form-section__title">Matériel</div>
        </div>

        <div class="form-grid form-grid--3col">

            <!-- Objet -->
            <div class="form-group">
                <label for="idObjet">Objet</label>
                <select name="idObjet" id="idObjet" class="form-control">
                    <option value="">— Choisir —</option>
                    <?php foreach ($objetOptions as $option) : ?>
                        <option value="<?= (int) $option['id'] ?>"
                            <?= (isset($_POST['idObjet']) && (int) $_POST['idObjet'] === (int) $option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Marque -->
            <div class="form-group">
                <label for="idMarque">Marque</label>
                <select name="idMarque" id="idMarque" class="form-control"
                        onchange="filterFamilles(this.value)">
                    <option value="">— Choisir —</option>
                    <?php foreach ($marqueOptions as $option) : ?>
                        <option value="<?= (int) $option['id'] ?>"
                            <?= (isset($_POST['idMarque']) && (int) $_POST['idMarque'] === (int) $option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Famille (filtrée par marque) -->
            <div class="form-group">
                <label for="idFamille">Famille</label>
                <select name="idFamille" id="idFamille" class="form-control">
                    <option value="">— Choisir une marque d'abord —</option>
                    <?php foreach ($familleOptions as $option) : ?>
                        <option value="<?= (int) $option['id'] ?>"
                                data-marque="<?= (int) $option['idMarque'] ?>"
                            <?= (isset($_POST['idFamille']) && (int) $_POST['idFamille'] === (int) $option['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
    </div>

    <!-- ══ SECTION 5 : Commentaire ══════════════════════════════ -->
    <div class="form-card">
        <div class="form-section">
            <div class="form-section__title">Commentaire</div>
        </div>

        <div class="form-group">
            <label for="commentaire">
                Description / notes
                <span class="label-hint">Optionnel</span>
            </label>
            <textarea name="commentaire" id="commentaire"
                      class="form-control"
                      style="min-height:130px"
                      placeholder="Décris le problème, les symptômes observés, les interventions déjà effectuées…"><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- ══ ACTIONS ══════════════════════════════════════════════ -->
    <div class="form-actions">
        <span class="form-actions__hint">
            <span>*</span> Champs obligatoires
        </span>
        <a href="index.php?p=listTicketsView" class="btn btn--ghost">
            Annuler
        </a>
        <button type="submit" class="btn btn--primary">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16.5 3.5l-9 9-4-4"/>
            </svg>
            Créer le ticket
        </button>
    </div>

</form>

<script>
// ── Indicateur de priorité ───────────────────────────────────
function setPrioIndicator(val) {
    document.getElementById('prio-wrapper').dataset.prio = val;
}
// Init au chargement (après erreur POST)
setPrioIndicator(document.getElementById('priorite').value);

// ── Filtre familles par marque ────────────────────────────────
const allFamilleOptions = Array.from(
    document.getElementById('idFamille').querySelectorAll('option[data-marque]')
);
const selectedFamilleId = <?= (int) ($_POST['idFamille'] ?? 0) ?>;

function filterFamilles(marqueId) {
    const sel    = document.getElementById('idFamille');
    const mid    = parseInt(marqueId) || 0;
    const before = parseInt(sel.value) || 0;

    sel.classList.add('filtering');

    // Vider et reconstruire
    sel.innerHTML = '<option value="">' + (mid ? '— Choisir une famille —' : '— Choisir une marque d\'abord —') + '</option>';

    allFamilleOptions
        .filter(opt => mid === 0 || parseInt(opt.dataset.marque) === mid)
        .forEach(opt => {
            const clone    = opt.cloneNode(true);
            clone.selected = parseInt(clone.value) === before;
            sel.appendChild(clone);
        });

    setTimeout(() => sel.classList.remove('filtering'), 150);
}

// Init au chargement
filterFamilles(document.getElementById('idMarque').value);
</script>

<?php
$content = ob_get_clean();
$title   = 'Nouveau ticket';
require('views/layout/baseLayout.php');
?>