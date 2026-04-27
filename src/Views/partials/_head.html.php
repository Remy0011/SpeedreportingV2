<?php

use Src\Services\AssetService;

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? "SR | $page_title" : 'Speed Reporting'; ?></title>
    <link rel="icon" type="image/x-icon" href="/assets/images/logos/favicon.ico">

    <!-- Fichier JS principal -->
    <script defer src="/assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Fichier CSS principal -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/boxicons.min.css">

    <!-- Fichiers CSS et JS additionnels -->
    <script type="module" src="/assets/js/functions.js"></script>

    <?php
    $styles = AssetService::getStyles();
    $scripts = AssetService::getScripts();

    foreach ($styles as $style): ?>
        <link rel="stylesheet" href="/assets/css/<?= htmlspecialchars($style, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>

    <?php foreach ($scripts as $script): ?>
        <script defer type="module" src="/assets/js/<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
</head>