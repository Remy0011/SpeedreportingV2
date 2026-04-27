<?php

$weekWork_data = $data['weekWork_data'];

$colorMax = [255, 127, 80];
$colorMin = [63, 49, 44];

// Find min and max total_hours
$hours = array_column($weekWork_data, 'total_hours');
$min = min($hours);
$max = max($hours);

$colors = [];
foreach ($weekWork_data as $week) {
    if ($max == $min) {
        $t = 0;
    } else {
        $t = ($week['total_hours'] - $min) / ($max - $min);
    }
    $colors[] = lerpColor($colorMin, $colorMax, $t);
}

?>

<div class="card" data-card="weekWork">
    <div class="card-container">
        <h2>Volume de travail par semaines</h2>
        <div>
            <canvas id="weekWork"></canvas>
        </div>
        <script>
            const ctx = document.getElementById('weekWork');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($weekWork_data as $week): ?>
                            '<?= $week['work_year'] ?> s <?= $week['work_week'] ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($weekWork_data as $week): ?>
                                <?= $week['total_hours'] ?>,
                            <?php endforeach; ?>
                        ],
                        label: 'Heures travaillées',
                        backgroundColor: [
                            <?php foreach ($colors as $color): ?> 'rgba(<?= $color[0] ?>, <?= $color[1] ?>, <?= $color[2] ?>, 0.2)',
                            <?php endforeach; ?>
                        ],
                        borderColor: [
                            <?php foreach ($colors as $color): ?> 'rgba(<?= $color[0] ?>, <?= $color[1] ?>, <?= $color[2] ?>, 1)',
                            <?php endforeach; ?>
                        ],
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
        </script>
        <div class="buttons">
            <a href="/heures" class="button primary">Voir plus</a>
        </div>
    </div>
</div>