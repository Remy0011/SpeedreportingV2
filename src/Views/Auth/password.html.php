<?php

use Src\Services\AssetService;
use Src\Services\CsrfService;

AssetService::addStyle("login.css");
AssetService::addScript("login.js");

require_once __DIR__ . '/../partials/_top.html.php';

?>

<section class="login">
    <div class="container">
        <div class="form-container sign-in">
            <form id="resetpwd-form" method="POST" action="/mdp-oublie">
                <h1>Mot de passe oublié</h1>
                <p>
                    Saisissez votre e-mail pour recevoir un lien de réinitialisation.
                </p>
                <div class="input-container">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div id="message"></div>
                <button type="submit">Envoyer</button>

                <input type="hidden" name="action" value="resetpwd">
                <?php CsrfService::insertToken(); ?>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/_bottom.html.php'; ?>