<?php

use Src\Services\AssetService;

AssetService::addScript(["_sidebar.js", "_breadcrumb.js", "_settings.js", "_help.js", "Table.js"]);

$page_title = "Planning prévisionnel";

require_once __DIR__ . '/../partials/_top.html.php';
include_once __DIR__ . '/../partials/_sidebar.html.php';
include_once __DIR__ . '/../partials/_breadcrumb.html.php';
?>

<section class="content">
    <h1>Planning prévisionnel</h1>
</section>

<?php require_once __DIR__ . '/../partials/_bottom.html.php'; ?>