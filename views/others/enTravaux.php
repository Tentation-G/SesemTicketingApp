<?php ob_start(); ?>

<div class="center_wrapper_content">
    <h1>Chantier</h1>
</div>

<?php
$content = ob_get_clean();
$title = "Chantier";
require('views/layout/baseLayout.php');
?>