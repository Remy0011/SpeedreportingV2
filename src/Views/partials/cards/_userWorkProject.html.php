<?php
$projects = $data['userWorkProject'] ?? [];
?>

<div class="card" data-card="userWorkProject">
    <div class="card-container">
        <h2>Projets sur lesquels vous avez travaillé récemment</h2>

        <?php if (empty($projects)) : ?>
            <div class="table-empty">
                Vous n'avez travaillé sur aucun projet récemment.
            </div>
        <?php else : ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Projets</th>
                            <th>Heures travaillées</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $index => $project): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($project['project_name']) ?></td>
                                <td><?= number_format($project['total_hours'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="buttons">
            <a href="/mes-heures" class="button primary">Voir plus</a>
        </div>
    </div>
</div>