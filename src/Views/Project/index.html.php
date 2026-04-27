<?php

use Src\Services\AssetService;

AssetService::addStyle(['_sidebar.css', '_breadcrumb.css', 'Modal.css', 'Table.css', 'Button.css', '_settings.css', '_help.css']);
AssetService::addScript(['_sidebar.js', '_breadcrumb.js', 'Table.js', '_settings.js', '_help.js']);

$page_title = "Projets";

require_once __DIR__ . '/../partials/_top.html.php';

include_once __DIR__ . '/../partials/_sidebar.html.php';
include_once __DIR__ . '/../partials/_breadcrumb.html.php';
?>

<section class="content">
    <h1>Projets</h1>
    <div id="table-container">
        <?php
        include_once __DIR__ . '/../partials/tables/_project.html.php';
        ?>
    </div>
</section>

<?php
require_once __DIR__ . '/../partials/_bottom.html.php';
?>