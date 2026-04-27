<?php

use Src\Services\AssetService;
use Src\Services\ComponentService;

AssetService::addStyle(["style.css", "_error.css", "Button.css"]);

$errorCode ??= '500';
$errorCode = htmlspecialchars($errorCode, ENT_QUOTES, 'UTF-8');
file_exists($_SERVER["DOCUMENT_ROOT"] . "/assets/images/error/$errorCode.svg") ? $svg_file = "/assets/images/error/$errorCode.svg" : $svg_file = "/assets/images/error/500.svg";

$page_title = "Erreur $errorCode";

require_once __DIR__ . '/partials/_top.html.php';

?>
<section class="error">
    <div class="cube cube1"></div>
    <div class="cube cube2"></div>
    <div class="cube cube3"></div>

    <div class="block-error">
        <h1>Erreur <?= $errorCode; ?></h1>
        <img src="<?= $svg_file; ?>" alt="Erreur <?= $errorCode; ?>">
        <p><?= $errorMessage ?? 'Une erreur est survenue. Veuillez réessayer plus tard.' ?></p>

        <div class="btn">
            <?= ComponentService::generateButton('/', 'Retour à l\'accueil'); ?>
        </div>
    </div>
</section>
<?php

require_once __DIR__ . '/partials/_bottom.html.php';

?>