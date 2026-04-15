<?php ob_start(); ?>

<div class="login-wrap">

    <div class="login-card">
        <h2 class="login-card__title">Connexion</h2>
        <p class="login-card__sub">Accès réservé aux collaborateurs</p>

        <?php if (!empty($error)) : ?>
        <div class="alert alert--error" style="margin-bottom:18px">
            <span class="alert__icon">⚠</span>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form action="index.php?p=connexion" method="post" novalidate>

            <div class="form-group">
                <label for="login">Identifiant</label>
                <input type="text" id="login" name="login"
                    class="form-control"
                    placeholder="votre.login"
                    autocomplete="username"
                    required>
            </div>

            <div class="form-group" style="margin-top:14px">
                <label for="pass">Mot de passe</label>
                <input type="password" id="pass" name="pass"
                    class="form-control"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required>
            </div>

            <button type="submit" class="btn btn--primary login-card__submit">
                Se connecter
            </button>

        </form>

        <div class="login-card__footer">
            Pas encore de compte ?
            <a href="index.php?p=inscriptionView">S'inscrire</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title   = 'Connexion';
require_once('./views/layout/baseLayout.php');
?>