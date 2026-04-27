<?php

use Src\Services\AuthService;
use Src\Services\BreadcrumbService;

$breadcrumbs = BreadcrumbService::getBreadcrumbs();

?>

<div class="breadcrumb">
    <div class="left">
        <h1 id="breadcrumb-title">Dashboard</h1>
        <ul id="breadcrumb-list">
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                <li class="breadcrumb-item">
                    <?php if ($breadcrumb["url"]): ?>
                        <a href="<?= htmlspecialchars($breadcrumb["url"]) ?>">
                            <?= htmlspecialchars($breadcrumb["name"]) ?>
                        </a>
                    <?php else: ?>
                        <span><?= htmlspecialchars($breadcrumb["name"]) ?></span>
                    <?php endif; ?>

                    <?php if ($index < count($breadcrumbs) - 1): ?>
                        <span class="breadcrumb-separator">></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="profile">
        <div class="info">
            <p id="greeting">Bonjour <span id="user-firstname"><?= AuthService::getUser()->getFirstname(); ?></span> !</p>
            <small class="text-muted" id="user-role"><?= AuthService::getRole()->getFr(); ?></small>
        </div>
        <div class="profile-photo" id="profile-photo">
            <a class="button view"><img src="<?= AuthService::getUser()->getPicture(); ?>" alt="Photo utilisateur"></a>
        </div>
    </div>
</div>