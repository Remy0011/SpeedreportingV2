<?php
$breakUserData = $data['haveBreakUser'];
$breakUserCount = count($breakUserData);
?>

<div class="card" data-card="haveBreakUser">
    <div class="card-container">
        <h2>Utilisateurs en congés / arrêt maladie</h2>

        <div class="counter-container">
            <div id="breakUserCounter" class="counter" data-count="<?= $breakUserCount ?>">0</div>
            <p class="counter-label">Nombre d'utilisateurs</p>
        </div>

        <div class="buttons">
            <a href="/heures" class="button primary">Voir plus</a>
        </div>
    </div>
</div>