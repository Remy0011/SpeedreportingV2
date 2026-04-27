<?php
$projectsByClientType = $data['projectsByClientType'] ?? [];
$projectsByClient = $data['projectsByClient'] ?? [];

$projectsByClientType = array_values(array_filter($projectsByClientType, fn($item) => !empty($item['client_type'])));
$projectsByClient = array_values(array_filter($projectsByClient, fn($item) => !empty($item['client_name'])));

$colorMax = [255, 127, 80];
$colorMin = [63, 49, 44];

function generateColors(array $data, string $valueKey, array $colorMin, array $colorMax)
{
    $values = array_column($data, $valueKey);
    $min = min($values);
    $max = max($values);

    $colors = [];
    foreach ($data as $item) {
        $t = ($max == $min) ? 0 : ($item[$valueKey] - $min) / ($max - $min);
        $rgb = lerpColor($colorMin, $colorMax, $t);
        $colors[] = $rgb;
    }
    return $colors;
}

$colorsByType = generateColors($projectsByClientType, 'project_count', $colorMin, $colorMax);
$colorsByClient = generateColors($projectsByClient, 'project_count', $colorMin, $colorMax);
?>

<div class="card" data-card="projectsByClient">
    <div class="card-container">
        <h2>Nombre de projets par type de client</h2>

        <label for="filterType">Filtrer par :</label>
        <select id="filterType">
            <option value="type" selected>Type de client</option>
            <option value="client">Client</option>
        </select>

        <canvas id="projectsByClientChart" style="height: 400px;"></canvas>

        <script>
            const ctx_pbc = document.getElementById('projectsByClientChart').getContext('2d');

            const dataByType = <?= json_encode($projectsByClientType) ?>;
            const dataByClient = <?= json_encode($projectsByClient) ?>;
            const colorsByType = <?= json_encode($colorsByType) ?>;
            const colorsByClient = <?= json_encode($colorsByClient) ?>;

            function rgbToRgba(rgbArray, alpha) {
                return `rgba(${rgbArray[0]}, ${rgbArray[1]}, ${rgbArray[2]}, ${alpha})`;
            }

            let currentFilter = 'type';
            let chart;

            function prepareBarData(data, colors, labelKey) {
                return {
                    labels: data.map(item => item[labelKey]),
                    datasets: [{
                        label: 'Nombre de projets',
                        data: data.map(item => parseInt(item.project_count)),
                        backgroundColor: colors.map(c => rgbToRgba(c, 0.2)),
                        borderColor: colors.map(c => rgbToRgba(c, 1)),
                        borderWidth: 1
                    }]
                };
            }

            function renderChart() {
                if (chart) {
                    chart.destroy();
                }

                let chartData, colors, labelKey;
                if (currentFilter === 'type') {
                    chartData = dataByType;
                    colors = colorsByType;
                    labelKey = 'client_type';
                } else {
                    chartData = dataByClient;
                    colors = colorsByClient;
                    labelKey = 'client_name';
                }

                chart = new Chart(ctx_pbc, {
                    type: 'bar',
                    data: prepareBarData(chartData, colors, labelKey),
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: currentFilter === 'type' ? 'Projets par type de client' : 'Projets par client'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' projets';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }
                    }
                });
            }

            document.getElementById('filterType').addEventListener('change', (e) => {
                currentFilter = e.target.value;
                renderChart();
            });

            renderChart();
        </script>
        <div class="buttons">
            <a href="/clients" class="button primary">Voir plus</a>
        </div>
    </div>
</div>