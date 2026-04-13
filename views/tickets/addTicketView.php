<?php ob_start(); ?>

<h1>Créer un ticket</h1>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="index.php?p=addTicket" method="post">

    <div>
        <label for="type">Type</label>
        <select name="type" id="type" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($typeOptions as $type) : ?>
                <option value="<?= htmlspecialchars($type) ?>">
                    <?= htmlspecialchars($type) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="priorite">Priorité</label>
        <select name="priorite" id="priorite" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($prioriteOptions as $id => $label) : ?>
                <option value="<?= (int) $id ?>">
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="nomClient">Nom client</label>
        <input type="text" name="nomClient" id="nomClient" required>
    </div>

    <div>
        <label for="adresse">Adresse</label>
        <input type="text" name="adresse" id="adresse" required>
    </div>

    <div>
        <label for="codePostal">Code postal</label>
        <input type="text" name="codePostal" id="codePostal" required>
    </div>

    <div>
        <label for="ville">Ville</label>
        <input type="text" name="ville" id="ville" required>
    </div>

    <div>
        <label for="adresseChantier">Adresse chantier</label>
        <textarea name="adresseChantier" id="adresseChantier"></textarea>
    </div>

    <div>
        <label for="numSite">Num site</label>
        <input type="text" name="numSite" id="numSite">
    </div>

    <div>
        <label for="numContrat">Num contrat</label>
        <input type="text" name="numContrat" id="numContrat">
    </div>

    <div>
        <label for="idObjet">Objet</label>
        <select name="idObjet" id="idObjet">
            <option value="">-- Choisir --</option>
            <?php foreach ($objetOptions as $option) : ?>
                <option value="<?= (int) $option['id'] ?>">
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
                <option value="<?= (int) $option['id'] ?>">
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
                <option value="<?= (int) $option['id'] ?>">
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
                <option value="<?= (int) $option['id'] ?>">
                    <?= htmlspecialchars($option['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="commentaire">Commentaire</label>
        <textarea name="commentaire" id="commentaire" required></textarea>
    </div>

    <div>
        <button type="submit">Créer le ticket</button>
    </div>
</form>

<?php
$content = ob_get_clean();
$title = "Créer un ticket";
require('views/layout/baseLayout.php');
?>