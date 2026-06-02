<?php

use Src\Services\AssetService;

$view = $_GET['view'] ?? 'project'; // Projets par défaut

AssetService::addStyle(["_sidebar.css", "_breadcrumb.css", "Button.css", "Modal.css", "_settings.css", "_help.css", "Table.css", "Card.css", "_toolbar.css"]);
AssetService::addScript(["_sidebar.js", "_breadcrumb.js", "_settings.js", "_help.js", "Table.js"]);

$page_title = "Planning prévisionnel";

require_once __DIR__ . '/../partials/_top.html.php';
include_once __DIR__ . '/../partials/_sidebar.html.php';
include_once __DIR__ . '/../partials/_breadcrumb.html.php';
?>

<section class="content">
    <h1>Planning prévisionnel</h1>
    <?php include_once __DIR__ . '/../partials/cards/_toolbar.html.php'; ?>
</section>

<section class="content">
    <?php
    match($view) {
        'users'    => include_once __DIR__ . '/user_vue.html.php',
    'calendar' => include_once __DIR__ . '/calendar_vue.html.php',
    default    => include_once __DIR__ . '/project_vue.html.php',
    };
    ?>
</section>

<?php require_once __DIR__ . '/../partials/_bottom.html.php'; ?>