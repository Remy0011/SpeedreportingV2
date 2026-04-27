<?php
$userWeekWork_data = $data['userWeekWork'];

$colorMax = [255, 127, 80];
$colorMin = [63, 49, 44];

$hours = array_column($userWeekWork_data, 'total_hours');
$min = min($hours);
$max = max($hours);

$colors = [];
foreach ($userWeekWork_data as $week) {
    $t = $max == $min ? 0 : ($week['total_hours'] - $min) / ($max - $min);
    $colors[] = lerpColor($colorMin, $colorMax, $t);
}

$labels = array_map(fn($w) => "{$w['work_year']} s {$w['work_week']}", $userWeekWork_data);
$dataPoints = array_column($userWeekWork_data, 'total_hours');
$backgroundColors = array_map(fn($c) => "rgba({$c[0]}, {$c[1]}, {$c[2]}, 0.2)", $colors);
$borderColors = array_map(fn($c) => "rgba({$c[0]}, {$c[1]}, {$c[2]}, 1)", $colors);
?>

<div class="card" data-card="userWeekWork">
    <div class="card-container">
        <h2>Volume de travail par semaines</h2>
        <div>
            <canvas id="weekWork"></canvas>
        </div>
        <div class="buttons">
            <a href="/mes-heures" class="button primary">Voir plus</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('weekWork');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Heures travaillées',
                data: <?= json_encode($dataPoints) ?>,
                backgroundColor: <?= json_encode($backgroundColors) ?>,
                borderColor: <?= json_encode($borderColors) ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
