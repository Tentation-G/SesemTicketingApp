<?php ob_start(); ?>

<div class="center_wrapper_content">
    <h1>Page en cours de developpement</h1>
    <img class="contour_img" src="assets/img/enConstruction.png" alt="">
</div>

<?php
$content = ob_get_clean();
$title = "Chantier";
require_once('views/layout/baseLayout.php');
?>