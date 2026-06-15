<?php

use Src\Services\AssetService;

AssetService::addStyle(["_sidebar.css", "_breadcrumb.css", "Button.css", "Modal.css", "_settings.css", "_help.css", "Calendar.css", "Card.css", "Planning.css", "Table.css", "_toolbar.css"]);
AssetService::addScript(["_sidebar.js", "_breadcrumb.js", "_settings.js", "_help.js", "Table.js"]);

$page_title = "Planning prévisionnel";

require_once __DIR__ . '/../partials/_top.html.php';
include __DIR__ . '/../partials/_sidebar.html.php';
include __DIR__ . '/../partials/_breadcrumb.html.php';
?>

    <section class="content">
        <h1>Planning prévisionnel</h1>

        <?php
        $underloaded_users = array_filter($user_rows ?? [], fn($row) => (float) $row['hours'] < 35);
        ?>
        <?php if (!empty($underloaded_users)): ?>
            <a href="?view=users<?= isset($selected_week['number']) ? '&week=' . $selected_week['number'] : '' ?><?= isset($months['current']['month']) ? '&month=' . $months['current']['month'] . '&year=' . $months['current']['year'] : '' ?>"
               class="planning-alert">
                <i class='bx bx-error-circle'></i>
                <span>
                <?= count($underloaded_users) ?>
                collaborateur<?= count($underloaded_users) > 1 ? 's' : '' ?>
                n'<?= count($underloaded_users) > 1 ? 'ont' : 'a' ?> pas atteint 35h cette semaine
            </span>
                <i class='bx bx-chevron-right'></i>
            </a>
        <?php endif; ?>

        <?php include __DIR__ . '/../partials/cards/_toolbar.html.php'; ?>
        <?php match($view) {
            'users'    => include __DIR__ . '/user_vue.html.php',
            'calendar' => include __DIR__ . '/week_calendar.html.php',
            default    => include __DIR__ . '/project_vue.html.php',
        }; ?>
    </section>

<?php require_once __DIR__ . '/../partials/_bottom.html.php'; ?>