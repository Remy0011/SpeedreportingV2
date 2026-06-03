<?php

use Src\Services\AssetService;

AssetService::addStyle(["_sidebar.css", "_breadcrumb.css", "Button.css", "Modal.css", "_settings.css", "_help.css", "Calendar.css", "Card.css", "Planning.css"]);
AssetService::addScript(["_sidebar.js", "_breadcrumb.js", "_settings.js", "_help.js"]);

$page_title = "Planning prévisionnel";

require_once __DIR__ . '/../partials/_top.html.php';
include_once __DIR__ . '/../partials/_sidebar.html.php';
include_once __DIR__ . '/../partials/_breadcrumb.html.php';
?>

<section class="content">
    <h1>Planning prévisionnel</h1>
    <?php include_once __DIR__ . '/week_calendar.html.php'; ?>
</section>

<?php require_once __DIR__ . '/../partials/_bottom.html.php'; ?>
