<?php

use Src\Services\AssetService;
use Src\Services\AuthService;

AssetService::addStyle(["_sidebar.css", "_breadcrumb.css", "Button.css", "Modal.css", "_settings.css", "_help.css", "Table.css", "Card.css"]);
AssetService::addScript(["_sidebar.js", "_breadcrumb.js", "_settings.js", "_help.js", "Table.js"]);

$page_title = "Heures";

require_once __DIR__ . '/../partials/_top.html.php';

include_once __DIR__ . '/../partials/_sidebar.html.php';
include_once __DIR__ . '/../partials/_breadcrumb.html.php';
?>

<section class="content">
    <h1>Horaires</h1>
    <?php
    if ($work_to_validate) {
        include __DIR__ . '/../partials/cards/_workToValidate.html.php';
    }
    ?>
    <div class="alert-container">

    </div>
    <div id="table-container">
        <?php
        include_once __DIR__ . '/../partials/tables/_work.html.php';
        ?>
    </div>
</section>

<?php
require_once __DIR__ . '/../partials/_bottom.html.php';
?>