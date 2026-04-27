<?php

use Src\Services\AuthService;

?>

<section class="section-settings">
    <div class="settings-container">
        <div class="nav-settings">
            <div class="nav-content">
                <ul>
                    <li data-settings="account">Compte</li>
                    <li data-settings="app">Application</li>
                    <li data-settings="security">Document</li>
                </ul>
            </div>
        </div>

        <div class="settings-content">
            <div class="account-settings" data-content="account">
                <h2>Compte</h2>
                <form>
                    <div class="form-content">
                        <label for="account-firstname">Prénom</label>
                        <input type="text" id="account-firstname" name="account-firstname" placeholder="<?= AuthService::getUser()->getFirstname(); ?>">
                    </div>

                    <div class="form-content">
                        <label for="account-lastname">Nom</label>
                        <input type="text" id="account-lastname" name="account-lastname" placeholder="<?= AuthService::getUser()->getLastname(); ?>">
                    </div>

                    <div class="form-content">
                        <label for="account-email">Mail</label>
                        <input type="email" id="account-email" name="account-email" placeholder="<?= AuthService::getUser()->getEmail(); ?>">
                    </div>

                    <div class="form-content">
                        <label for="account-role">Role</label>
                        <input type="text" id="account-role" name="account-role" placeholder="<?= AuthService::getRole()->getName(); ?>">
                    </div>
                </form>
            </div>

            <div class="app-settings" data-content="app">
                <h2>Application</h2>
                <form>
                    <div class="form-content">
                        <h3>Mode Sombre/Clair</h3>
                        <p>Passage du mode Sombre à Clair</p>
                        <label class="switch">
                            <input type="checkbox" id="theme-switch">
                            <span class="slider"></span>
                        </label>
                    </div>
                </form>
            </div>

            <div class="security-settings" data-content="security">
                <h2>Document</h2>
                <div class="form-content">
                    <h3>Documentation</h3>
                    <p>Utilisateur</p>
                    <a href="/public/doc/Documentation_Utilisateur_SpeedReporting.pdf" class="button success settings" title="Télécharger la documentation utilisateur" download><i class='bx bx-download'></i></a>
                </div>
            </div>
        </div>
    </div>
</section>