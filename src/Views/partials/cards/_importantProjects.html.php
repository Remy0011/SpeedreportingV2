<?php
$projects = $data['importantProjects'] ?? [];

function getProgressColor($percent)
{
    if ($percent >= 90) return 'rgba(255, 37, 37, 0.2)';
    if ($percent >= 50) return 'rgba(255, 90, 31, 0.2)';
    return 'rgba(40, 167, 69, 0.2)';
}

function adjustOpacity($rgba, $newOpacity)
{
    sscanf($rgba, 'rgba(%d, %d, %d, %f)', $r, $g, $b, $a);
    return "rgba($r, $g, $b, $newOpacity)";
}

function calculateProjectData($project)
{
    $start = new DateTime($project['project_start']);
    $end = new DateTime($project['project_end']);
    $today = new DateTime();

    $totalDays = $start->diff($end)->days;
    $daysPassed = $start->diff($today)->days;
    $daysLeft = $end->diff($today)->invert === 1 ? $end->diff($today)->days : 0;

    $progress = min(100, max(0, round(($daysPassed / $totalDays) * 100)));
    $color = getProgressColor($progress);
    $borderColor = adjustOpacity($color, 0.8);

    return [
        'name' => $project['project_name'],
        'start' => $start->format('d/m/Y'),
        'end' => $end->format('d/m/Y'),
        'days_left' => $daysLeft,
        'progress' => $progress,
        'color' => $color,
        'borderColor' => $borderColor
    ];
}

$displayProjects = array_map('calculateProjectData', $projects);
?>

<div class="card" data-card="importantProjects">
    <div class="card-container">
        <h2>Projets urgents</h2>
        <div>
            <canvas id="urgentProjectsChart"></canvas>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('urgentProjectsChart').getContext('2d');

                const daysLeftData = <?= json_encode(array_column($displayProjects, 'days_left')) ?>;

                <?php
                $maxLength = 15;
                $truncatedLabels = array_map(function ($name) {
                    return shortenDescription($name, 15);
                }, array_column($displayProjects, 'name'));
                ?>

                const fullNames = <?= json_encode(array_column($displayProjects, 'name')) ?>;

                const data = {
                    labels: <?= json_encode($truncatedLabels) ?>,
                    datasets: [{
                        label: 'Urgence (%)',
                        data: <?= json_encode(array_column($displayProjects, 'progress')) ?>,
                        backgroundColor: <?= json_encode(array_column($displayProjects, 'color')) ?>,
                        borderColor: <?= json_encode(array_column($displayProjects, 'borderColor')) ?>,
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                };

                function splitTextByWords(text, maxLineLength = 30) {
                    const words = text.split(' ');
                    let lines = [];
                    let currentLine = '';

                    for (let word of words) {
                        if ((currentLine + word).length <= maxLineLength) {
                            currentLine += (currentLine ? ' ' : '') + word;
                        } else {
                            lines.push(currentLine);
                            currentLine = word;
                        }
                    }

                    if (currentLine) {
                        lines.push(currentLine);
                    }

                    return lines;
                }

                new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Avancement des projets urgents'
                            },
                            tooltip: {
                                callbacks: {
                                    title: function() {
                                        return '';
                                    },
                                    label: function(context) {
                                        const index = context.dataIndex;
                                        const fullLabel = fullNames[index] || '';
                                        const value = context.raw;
                                        const daysLeft = <?= json_encode(array_column($displayProjects, 'days_left')) ?>;
                                        const splitLabel = splitTextByWords(fullLabel, 50);

                                        splitLabel[splitLabel.length - 1] += ' :';
                                        return [...splitLabel, `${value}% (reste ${daysLeft[index]} j)`];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    callback: value => value + '%'
                                }
                            }
                        }
                    }
                });
            });
        </script>

        <div class="buttons">
            <a href="/projets" class="button primary">Voir plus</a>
        </div>
    </div>
</div>