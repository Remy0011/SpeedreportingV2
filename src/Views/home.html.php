<?php

use Src\Services\AuthService;
use Src\Services\AssetService;

AssetService::addStyle(["_sidebar.css", "_breadcrumb.css", "Card.css", "Button.css", "_settings.css", "_help.css"]);
AssetService::addScript(["functions.js", "_sidebar.js", "Card.js", "_breadcrumb.js", "_settings.js", "_help.js"]);

$page_title = "Accueil";

require_once __DIR__ . '/partials/_top.html.php';

include_once __DIR__ . '/partials/_sidebar.html.php';
include_once __DIR__ . '/partials/_breadcrumb.html.php';

?>

<section class="content">
    <h1>Home</h1>

    <!-- Card Menu -->
    <div class="card-settings">
        <button id="toggleCardMenu" class="icon-button"><i class='bx bx-dots-vertical-rounded'></i></button>
        <div id="cardMenu" class="card-menu hidden">
            <?php if (AuthService::isAdmin()) { ?>
                <div class="input-menu">
                    <label>Saisie à valider <input type="checkbox" data-target="workToValidate" checked></label>
                </div>
                <div class="input-menu">
                    <label>Volume hebdo <input type="checkbox" data-target="weekWork" checked></label>
                </div>
                <div class="input-menu">
                    <label>Statuts projets <input type="checkbox" data-target="projectStatus" checked></label>
                </div>
                <div class="input-menu">
                    <label>Projets importants <input type="checkbox" data-target="importantProjects" checked></label>
                </div>
                <div class="input-menu">
                    <label>Projets par client <input type="checkbox" data-target="projectsByClient" checked></label>
                </div>
                <div class="input-menu">
                    <label>En congés <input type="checkbox" data-target="haveBreakUser" checked></label>
                </div>
            <?php } else { ?>
                <div class="input-menu">
                    <label>Saisie à faire <input type="checkbox" data-target="workToEnter" checked></label>
                </div>
                <div class="input-menu">
                    <label>Heures de travail par semaine <input type="checkbox" data-target="userWeekWork" checked></label>
                </div>
                <div class="input-menu">
                    <label>Projets sur lesquels vous avez travaillé <input type="checkbox" data-target="userWorkProject" checked></label>
                </div>
            <?php }; ?>
        </div>
    </div>

    <!-- Dashboard Card -->
    <div class="cards">
        <?php
        if (isset($data['hoursReminder']) && $data['hoursReminder']) {
            require_once __DIR__ . '/partials/cards/_workToEnter.html.php';
        }
        if (isset($data['work_to_validate']) && $data['work_to_validate']) {
            require_once __DIR__ . '/partials/cards/_workToValidate.html.php';
        }
        if (isset($data['weekWork_data']) && !empty($data['weekWork_data'])) {
            require_once __DIR__ . '/partials/cards/_weekWork.html.php';
        }
        if (isset($data['projectStatus_data']) && !empty($data['projectStatus_data'])) {
            require_once __DIR__ . '/partials/cards/_projectStatus.html.php';
        }
        if (isset($data['importantProjects']) && !empty($data['importantProjects'])) {
            require_once __DIR__ . '/partials/cards/_importantProjects.html.php';
        }
        if (isset($data['projectsByClientType']) && isset($data['projectsByClient']) && !empty($data['projectsByClientType']) && !empty($data['projectsByClient'])) {
            require_once __DIR__ . '/partials/cards/_projectsByClient.html.php';
        }
        if (isset($data['haveBreakUser']) && !empty($data['haveBreakUser'])) {
            require_once __DIR__ . '/partials/cards/_haveBreakUser.html.php';
        }
        if (isset($data['userWeekWork']) && !empty($data['userWeekWork'])) {
            require_once __DIR__ . '/partials/cards/_userWeekWork.html.php';
        }
        if (isset($data['userWorkProject']) && !empty($data['userWorkProject'])) {
            require_once __DIR__ . '/partials/cards/_userWorkProject.html.php';
        }

        ?>
    </div>
</section>

<?php
require_once __DIR__ . '/partials/_bottom.html.php';
?>