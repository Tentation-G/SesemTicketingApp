<?php ob_start(); ?>

<div class="">
    <div class="">
        <h2>Inscription</h2>

        <?php if (!empty($error)) : ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="index.php?p=inscription" method="post">
            <div class="">
                <label for="login">Identifiant</label>
                <input type="text" id="login" name="login" required>
            </div>

            <div class="">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <div class="">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>

            <div class="">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="">
                <label for="pass">Mot de passe</label>
                <input type="password" id="pass" name="pass" required>
            </div>

            <div class="">
                <label for="pass_confirm">Confirmer le mot de passe</label>
                <input type="password" id="pass_confirm" name="pass_confirm" required>
            </div>

            <div class="">
                <input type="submit" value="S'inscrire">
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Inscription";
require('./views/layout/baseLayout.php');
?>