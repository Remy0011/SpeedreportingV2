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
            <form id="resetpwd-form" method="POST" action="/reset-mdp">
                <h1>Réinitialisation de mot de passe</h1>
                <p>
                    Veuillez saisir votre nouveau mot de passe.
                </p>
                <div class="input-container">
                    <label for="email">Email</label>
                    <input type="email" name="email" readonly value="<?= htmlspecialchars($email); ?>" required>
                </div>
                <div class="input-container">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                <div class="input-container">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirm" required>
                </div>
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
                <button type="submit">Envoyer</button>

                <input type="hidden" name="action" value="resetpwd">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                <?php CsrfService::insertToken(); ?>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../partials/_bottom.html.php'; ?>