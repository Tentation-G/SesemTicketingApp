<?php ob_start(); ?>

<!--<link rel="stylesheet" href="LIEN CSS">-->



<div class="">
    <div class="">
        <h2 class="">Connexion</h2>
        <form action="index.php?p=connexion" method="post">

            <?php if (!empty($error)) : ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="">
                <input type="text" id="login" name="login" required="">
                <label for="login">identifiant</label>
            </div>
            <div class="">
                <input type="password" id="pass" name="pass" required="">
                <label for="pass">Mot de Passe</label>
            </div>
            <div class="">
                <input type="submit" name="bouton" value="Se connecter" class="">
            </div>
            
        </form>
    </div>
</div>

<?php
    $content = ob_get_clean();
    $title = "ConnexionViewPage";
    require('./views/layout/baseLayout.php');
?>