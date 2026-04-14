<?php ob_start(); ?>

<h1>Créer un ticket</h1>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="index.php?p=addTicket" method="post">

    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <div>
        <label for="type">Type <span style="color:red">*</span></label>
        <select name="type" id="type" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($typeOptions as $type) : ?>
                <option value="<?= htmlspecialchars($type) ?>"
                    <?= (($_POST['type'] ?? '') === $type) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="priorite">Priorité <span style="color:red">*</span></label>
        <select name="priorite" id="priorite" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($prioriteOptions as $id => $label) : ?>
                <option value="<?= (int) $id ?>"
                    <?= (isset($_POST['priorite']) && (int) $_POST['priorite'] === $id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="nomClient">Nom client <span style="color:red">*</span></label>
        <input type="text" name="nomClient" id="nomClient" required
                value="<?= htmlspecialchars($_POST['nomClient'] ?? '') ?>">
    </div>

    <div>
        <label for="adresse">Adresse <span style="color:red">*</span></label>
        <input type="text" name="adresse" id="adresse" required
                value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
    </div>

    <div>
        <label for="codePostal">Code postal <span style="color:red">*</span></label>
        <input type="text" name="codePostal" id="codePostal" required
                value="<?= htmlspecialchars($_POST['codePostal'] ?? '') ?>">
    </div>

    <div>
        <label for="ville">Ville <span style="color:red">*</span></label>
        <input type="text" name="ville" id="ville" required
                value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>">
    </div>

    <div>
        <label for="adresseChantier">Adresse chantier</label>
        <textarea name="adresseChantier" id="adresseChantier"><?= htmlspecialchars($_POST['adresseChantier'] ?? '') ?></textarea>
    </div>

    <div>
        <label for="numSite">Num site</label>
        <input type="text" name="numSite" id="numSite"
                value="<?= htmlspecialchars($_POST['numSite'] ?? '') ?>">
    </div>

    <div>
        <label for="numContrat">Num contrat</label>
        <input type="text" name="numContrat" id="numContrat"
                value="<?= htmlspecialchars($_POST['numContrat'] ?? '') ?>">
    </div>

    <div>
        <label for="idObjet">Objet</label>
        <select name="idObjet" id="idObjet">
            <option value="">-- Choisir --</option>
            <?php foreach ($objetOptions as $option) : ?>
                <option value="<?= (int) $option['id'] ?>"
                    <?= (isset($_POST['idObjet']) && (int) $_POST['idObjet'] === (int) $option['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="idTypeClient">Type client</label>
        <select name="idTypeClient" id="idTypeClient">
            <option value="">-- Choisir --</option>
            <?php foreach ($typeClientOptions as $option) : ?>
                <option value="<?= (int) $option['id'] ?>"
                    <?= (isset($_POST['idTypeClient']) && (int) $_POST['idTypeClient'] === (int) $option['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="idMarque">Marque</label>
        <select name="idMarque" id="idMarque">
            <option value="">-- Choisir --</option>
            <?php foreach ($marqueOptions as $option) : ?>
                <option value="<?= (int) $option['id'] ?>"
                    <?= (isset($_POST['idMarque']) && (int) $_POST['idMarque'] === (int) $option['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="idFamille">Famille</label>
        <select name="idFamille" id="idFamille">
            <option value="">-- Choisir --</option>
            <?php foreach ($familleOptions as $option) : ?>
                <option value="<?= (int) $option['id'] ?>"
                    <?= (isset($_POST['idFamille']) && (int) $_POST['idFamille'] === (int) $option['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="commentaire">Commentaire</label>
        <textarea name="commentaire" id="commentaire"><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
    </div>

    <div>
        <button type="submit">Créer le ticket</button>
    </div>
</form>

<!-- Filtre dynamique familles par marque -->
<script>
(function () {
    const allFamilles = <?= json_encode(array_map(function($f) {
        return ['id' => (int)$f['id'], 'label' => $f['label'], 'idMarque' => (int)$f['idMarque']];
    }, $familleOptions)) ?>;

    const marqueSelect  = document.getElementById('idMarque');
    const familleSelect = document.getElementById('idFamille');
    const selectedFamille = <?= (int) ($_POST['idFamille'] ?? 0) ?>;

    function filterFamilles() {
        const marqueId = parseInt(marqueSelect.value) || 0;
        const current  = parseInt(familleSelect.value) || 0;

        familleSelect.innerHTML = '<option value="">-- Choisir --</option>';

        allFamilles
            .filter(f => marqueId === 0 || f.idMarque === marqueId)
            .forEach(f => {
                const opt      = document.createElement('option');
                opt.value      = f.id;
                opt.textContent = f.label;
                if (f.id === current) opt.selected = true;
                familleSelect.appendChild(opt);
            });
    }

    marqueSelect.addEventListener('change', filterFamilles);
    filterFamilles(); // applique dès le chargement
})();
</script>

<?php
$content = ob_get_clean();
$title   = "Créer un ticket";
require('views/layout/baseLayout.php');
?>