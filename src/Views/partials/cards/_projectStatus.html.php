<?php
$projectsStatus_data = $data['projectStatus_data'] ?? [];

$labelMapping = [
    'termine'   => 'Validé',
    'en_cours'  => 'En cours',
    'annule'    => 'Annulé'
];

$statusMap = [
    'Validé' => 0,
    'En cours' => 0,
    'Annulé' => 0
];

foreach ($projectsStatus_data as $status) {
    $key = $status['project_status'] ?? '';
    if (isset($labelMapping[$key])) {
        $label = $labelMapping[$key];
        $statusMap[$label] = (int) $status['status_count'];
    }
}
?>

<div class="card" data-card="projectStatus">
    <div class="card-container">
        <h2>Status des projets</h2>
        <div>
            <canvas id="projectStatus"></canvas>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('projectStatus').getContext('2d');
                const projectStatusData = {
                    labels: ['Validé', 'En cours', 'Annulé'],
                    datasets: [{
                        label: 'Nombre de projets',
                        data: [
                            <?= $statusMap['Validé'] ?>,
                            <?= $statusMap['En cours'] ?>,
                            <?= $statusMap['Annulé'] ?>
                        ],
                        backgroundColor: [
                            'rgba(255, 127, 80, 0.2)',
                            'rgba(8, 29, 217, 0.2)',
                            'rgba(73, 76, 80, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 127, 80, 0.4)',
                            'rgba(8, 29, 217, 0.4)',
                            'rgba(73, 76, 80, 0.4)'
                        ],
                        borderWidth: 1
                    }]
                };

                new Chart(ctx, {
                    type: 'pie',
                    data: projectStatusData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Répartition des projets par statut'
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