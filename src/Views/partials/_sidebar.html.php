<?php

use Src\Services\AuthService;

$role = AuthService::getRole()->getId();

?>

<nav class="navbar">
    <div class="sidebar-backdrop"></div>
    <!-- Bouton de menu pour mobile -->
    <div class="menu-btn">
        <i class='bx bx-chevron-right icon'></i>
    </div>
    <aside class="sidebar">

        <!-- Logo et nom de l'application -->
        <div class="head">
            <div class="logo-details">
                <a href="/">
                    <img src="/assets/images/logos/logo-light.png" alt="Logo Speed Reporting" class="logo-details-img"
                        data-logo-big="/assets/images/logos/logo-light-big.png"
                        data-logo-small="/assets/images/logos/logo-light-small.png"
                        data-logo-dark="/assets/images/logos/logo-dark-big.png">
                </a>
            </div>
        </div>

        <!-- Menu principal -->
        <div class="nav">
            <div class="menu">
                <p class="title">Principal</p>
                <ul>
                    <li>
                        <a href="/">
                            <i class="bx bx-home"></i>
                            <span class="text">Dashboard</span>
                        </a>
                    </li>
                    <?php if ($role == 1): ?>
                        <li>
                            <a href="/heures">
                                <i class="bx bx-briefcase-alt-2"></i>
                                <span class="text">Horaires</span>
                            </a>
                        </li>
                        <li>
                            <a href="/projets">
                                <i class="bx bx-file"></i>
                                <span class="text">Projets</span>
                            </a>
                        </li>
                        <li>
                            <a href="/utilisateurs">
                                <i class="bx bx-user"></i>
                                <span class="text">Utilisateurs</span>
                            </a>
                        </li>
                        <li>
                            <a href="/clients">
                                <i class='bx bx-buildings'></i>
                                <span class="text">Clients</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($role == 2): ?>
                        <li>
                            <a href="/mes-heures">
                                <i class="bx bx-briefcase-alt-2"></i>
                                <span class="text">Horaires</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Menu de paramètres -->
        <div class="menu">
            <p class="title">Compte</p>
            <ul>
                <li>
                    <a href="/deconnexion" class="ajax-link">
                        <i class='bx bx-log-out' style='color:#e1051e'></i>
                        <span class="text">Déconnexion</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="ajax-link" id="settings-button">
                        <i class='bx bx-cog'></i>
                        <span class="text">Paramètres</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="ajax-link" id="help-button">
                        <i class='bx bx-question-mark'></i>
                        <span class="text">Aide</span>
                    </a>
                </li>
                <!-- Easter Egg -->
                <li>
                    <a href="#" id="easter-egg-button" class="easter-trigger" title="Activer le robot secret">
                        <p>🤖</p>
                        <span class="text">Pas touche !</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
</nav>
<div class="toast-container" id="toast-container"></div>
<?php include_once __DIR__ . "/_settings.html.php" ?>
<?php include_once __DIR__ . "/_help.html.php" ?>