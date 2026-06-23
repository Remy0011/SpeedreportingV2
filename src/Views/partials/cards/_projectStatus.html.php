<?php
$projectsStatus_data = $data['projectStatus_data'] ?? [];

$labelMapping = [
        'en_attente' => 'En attente',
        'en_cours'   => 'En cours',
        'termine'    => 'Terminé',
        'suspendu'   => 'Suspendu',
];

$statusMap = [
        'En attente' => 0,
        'En cours'   => 0,
        'Terminé'    => 0,
        'Suspendu'   => 0,
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
                    labels: ['En attente', 'En cours', 'Terminé', 'Suspendu'],
                    datasets: [{
                        label: 'Nombre de projets',
                        data: [
                            <?= $statusMap['En attente'] ?>,
                            <?= $statusMap['En cours'] ?>,
                            <?= $statusMap['Terminé'] ?>,
                            <?= $statusMap['Suspendu'] ?>
                        ],
                        backgroundColor: [
                            'rgba(108, 117, 125, 0.2)',
                            'rgba(8, 29, 217, 0.2)',
                            'rgba(40, 167, 69, 0.2)',
                            'rgba(220, 53, 69, 0.2)'
                        ],
                        borderColor: [
                            'rgba(108, 117, 125, 0.4)',
                            'rgba(8, 29, 217, 0.4)',
                            'rgba(40, 167, 69, 0.4)',
                            'rgba(220, 53, 69, 0.4)'
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
