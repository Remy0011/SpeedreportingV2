<?php

use Src\Services\AssetService;
use Src\Services\AuthService;

AssetService::addStyle(["_sidebar.css", "_breadcrumb.css", "Button.css", "Modal.css", "_hoursEntry.css", "_settings.css", "_help.css", "Calendar.css", "Card.css"]);
AssetService::addScript(["_sidebar.js", "_breadcrumb.js", "_settings.js", "_help.js", "Calendar.js"]);

$page_title = "Mes heures";

require_once __DIR__ . '/../partials/_top.html.php';

include_once __DIR__ . '/../partials/_sidebar.html.php';
include_once __DIR__ . '/../partials/_breadcrumb.html.php';
?>

<section class="content">
    <h1>Horaires</h1>
    <?php include_once __DIR__ . '/../partials/calendar/_full.html.php'; ?>
</section>

<?php
require_once __DIR__ . '/../partials/_bottom.html.php';
?>