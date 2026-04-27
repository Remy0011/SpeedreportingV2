<?php

use Src\Services\AssetService;
use Src\Services\CsrfService;

AssetService::addStyle("login.css");
AssetService::addScript("login.js");

$page_title = "Connexion";

require_once __DIR__ . '/../partials/_top.html.php';

?>

<section class="login">
    <div class="container">
        <div class="form-container sign-in">
            <form id="login-form" method="POST" action="/connexion">
                <!-- <div class="social-icons">
                    <a href="#" class="icon"><i class='bx bxl-google-plus'></i></a>
                    <a href="#" class="icon"><i class='bx bxl-facebook'></i></a>
                    <a href="#" class="icon"><i class='bx bxl-github'></i></a>
                    <a href="#" class="icon"><i class='bx bxl-linkedin'></i></a>
                </div>
                <span>ou utilisez votre email et votre mot de passe</span> -->
                <img src="/assets/images/logos/logo-light-small.png" alt="Logo Speedreporting" height="172px" />
                <h1>Se connecter</h1>
                <div class="input-container">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-container">
                    <label for="password">Mot de Passe</label>
                    <input type="password" name="password" placeholder="Mot de Passe" required>
                </div>
                <a href="/mdp-oublie">Vous avez oublié votre mot de passe ?</a>
                <div id="message">
                    <?php if (isset($message)): ?>
                        <p class="message">
                            <?= $message ?>
                        </p>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <p class="message error">
                            <?= $error ?>
                        </p>
                    <?php endif; ?>
                </div>
                <button type="submit">Se connecter</button>

                <?php CsrfService::insertToken(); ?>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/_bottom.html.php'; ?>