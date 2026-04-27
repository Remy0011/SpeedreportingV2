<?php
$progress = $data['hoursReminder']['progress'];
$day = (int) $data['hoursReminder']['day'];

function getStatusColor($day)
{
    if ($day <= 2) {
        return 'var(--status-success)';
    } elseif ($day === 3) {
        return 'var(--status-progress)';
    } elseif ($day >= 4 && $day <= 5) {
        return 'var(--status-danger)';
    } else {
        return 'var(--status-offline)';
    }
}

$color = getStatusColor($day);
?>

<div class="card" data-card="workToEnter">
    <div class="card-container">
        <h2>Rappel saisie des heures</h2>
        <p>Vous devez saisir et valider vos heures cette semaine.</p>

        <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?= $progress ?>%; background-color: <?= $color ?>;"></div>
        </div>

        <p>
            Progression vers la fin de semaine : <strong><?= $progress ?>%</strong>
        </p>

        <div class="buttons">
            <a href="/mes-heures" class="button primary">Saisir mes heures</a>
        </div>
    </div>
</div>