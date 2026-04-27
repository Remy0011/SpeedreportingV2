<?php

use Src\Services\AssetService;
use Src\Services\AuthService;

AssetService::addStyle(["_sidebar.css", "_breadcrumb.css", "Table.css", "Button.css", "Modal.css",  "_settings.css", "_help.css", "Card.css", "_validate.css"]);
AssetService::addScript(["_sidebar.js", "_breadcrumb.js", "Table.js",  "_settings.js", "_help.js", "_validate.js"]);

$page_title = "Validation des heures";

require_once __DIR__ . '/../partials/_top.html.php';

include_once __DIR__ . '/../partials/_sidebar.html.php';
include_once __DIR__ . '/../partials/_breadcrumb.html.php';
?>

<section class="content">
    <h1>Horaires</h1>

    <?php

    require_once __DIR__ . '/../partials/tables/_validate.html.php';
    ?>

</section>

<?php
require_once __DIR__ . '/../partials/_bottom.html.php';
?>