<?php
// Semaine sélectionnée (première semaine du mois par défaut)
$selected_week_number = $_GET['week'] ?? $weeks[0]['number'];
$current_week = null;
foreach ($weeks as $w) {
    if ($w['number'] == $selected_week_number) {
        $current_week = $w;
        break;
    }
}
// Fallback sur la première semaine si non trouvée
if (!$current_week) $current_week = $weeks[0];

// Jours Lundi → Vendredi uniquement
$week_days = array_filter($current_week['days'], fn($d) => !in_array($d['name'], ['Samedi', 'Dimanche']));

// Semaines précédente et suivante pour la navigation
$weeks_list    = array_values($weeks);
$current_index = array_search($current_week, $weeks_list);
$prev_week     = $current_index > 0 ? $weeks_list[$current_index - 1] : null;
$next_week     = $current_index < count($weeks_list) - 1 ? $weeks_list[$current_index + 1] : null;

// Vue courante (pour conserver la vue dans les liens de navigation)
$current_view = htmlspecialchars($view ?? 'projects', ENT_QUOTES);
?>

<div id="project-planning">

    <!-- Navigation -->
    <div class="header">
        <h2 class="calendar-title"><?= $months['current']['name'] ?> <?= $months['current']['year'] ?></h2>
        <nav class="calendar-nav">
            <!-- Mois précédent -->
            <a title="Mois précédent" class="pagination-link calendar-button"
               href="?view=<?= $current_view ?>&month=<?= $months['previous']['month'] ?>&year=<?= $months['previous']['year'] ?>">
                <i class='bx bxs-chevron-left'></i>
            </a>
            <!-- Mois suivant -->
            <a title="Mois suivant" class="pagination-link calendar-button"
               href="?view=<?= $current_view ?>&month=<?= $months['next']['month'] ?>&year=<?= $months['next']['year'] ?>">
                <i class='bx bxs-chevron-right'></i>
            </a>

            <!-- Semaine précédente -->
            <?php if ($prev_week): ?>
                <a title="Semaine précédente" class="pagination-link calendar-button"
                   href="?view=<?= $current_view ?>&month=<?= $months['current']['month'] ?>&year=<?= $months['current']['year'] ?>&week=<?= $prev_week['number'] ?>">
                    <i class='bx bx-chevron-left'></i>
                </a>
            <?php endif; ?>

            <div class="week-label">
                <strong>N° <?= $current_week['number'] ?></strong>
                <span><?= $current_week['range'] ?></span>
            </div>

            <!-- Semaine suivante -->
            <?php if ($next_week): ?>
                <a title="Semaine suivante" class="pagination-link calendar-button"
                   href="?view=<?= $current_view ?>&month=<?= $months['current']['month'] ?>&year=<?= $months['current']['year'] ?>&week=<?= $next_week['number'] ?>">
                    <i class='bx bx-chevron-right'></i>
                </a>
            <?php endif; ?>

            <!-- Retour mois en cours -->
            <?php if (
                $months['current']['year'] !== $months['today']['year'] ||
                $months['current']['month'] !== $months['today']['month']
            ): ?>
                <a title="Mois en cours" class="calendar-button"
                   href="?view=<?= $current_view ?>&month=<?= $months['today']['month'] ?>&year=<?= $months['today']['year'] ?>">
                    Mois en cours
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Tableau projets × jours -->
    <table id="planning-table">
        <thead>
            <tr>
                <th>Projets</th>
                <?php foreach ($week_days as $day): ?>
                    <th><?= $day['name'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td class="project-cell">
                        <span class="project-name"><?= $project->getName() ?></span>
                        <span class="project-devs"><?= $project->getResource() ?> développeurs</span>
                        <div class="project-actions">
                            <a href="#" class="button danger" data-modal="delete_project_<?= $project->getId() ?>">
                                <i class='bx bx-trash'></i>
                            </a>
                            <a href="#" class="button primary" data-modal="read_project_<?= $project->getId() ?>">
                                <i class='bx bx-show'></i>
                            </a>
                        </div>
                    </td>
                    <?php foreach ($week_days as $day): ?>
                        <?php $entry = $planning_data[$project->getId()][$day['date']] ?? null; ?>
                        <td class="day-cell <?= $entry ? 'has-entry' : '' ?> <?= $day['is_today'] ? 'is-today' : '' ?>">
                            <?php if ($entry): ?>
                                <span class="hours-count"><?= $entry->getCount() ?>H</span>
                                <div class="hour-card-actions">
                                    <a href="#" class="button contrast" data-modal="edit_<?= $entry->getId() ?>">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="#" class="button danger" data-modal="delete_<?= $entry->getId() ?>">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="#" class="btn-add"
                                   data-modal="create_<?= $project->getId() ?>_<?= $day['date'] ?>">
                                    <i class='bx bx-plus'></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modals -->
    <div class="modals">
        <?php foreach ($projects as $project): ?>

            <!-- Modal delete projet -->
            <?php require __DIR__ . '/../../partials/modals/delete/_top.html.php'; ?>
            <input type="hidden" name="project_id" value="<?= $project->getId() ?>">
            <?php require __DIR__ . '/../../partials/modals/delete/_bottom.html.php'; ?>

            <!-- Modal read projet -->
            <?php require __DIR__ . '/../../partials/modals/read/_top.html.php'; ?>
            <p><strong>Projet :</strong> <?= $project->getName() ?></p>
            <p><strong>Développeurs :</strong> <?= $project->getResource() ?></p>
            <?php require __DIR__ . '/../../partials/modals/read/_bottom.html.php'; ?>

            <?php foreach ($week_days as $day): ?>
                <?php $entry = $planning_data[$project->getId()][$day['date']] ?? null; ?>

                <?php if ($entry): ?>

                    <!-- Modal edit entrée -->
                    <?php require __DIR__ . '/../../partials/modals/edit/_top.html.php'; ?>
                    <input type="hidden" name="work_id" value="<?= $entry->getId() ?>">
                    <div class="input-container">
                        <label for="work_count_<?= $entry->getId() ?>">Heures prévues :</label>
                        <input type="number"
                               name="work_count"
                               id="work_count_<?= $entry->getId() ?>"
                               min="0.5" step="0.5"
                               value="<?= $entry->getCount() ?>" required>
                    </div>
                    <?php require __DIR__ . '/../../partials/modals/edit/_bottom.html.php'; ?>

                    <!-- Modal delete entrée -->
                    <?php require __DIR__ . '/../../partials/modals/delete/_top.html.php'; ?>
                    <input type="hidden" name="work_id" value="<?= $entry->getId() ?>">
                    <?php require __DIR__ . '/../../partials/modals/delete/_bottom.html.php'; ?>

                <?php else: ?>

                    <!-- Modal create entrée -->
                    <div class="modal" id="modal_create_<?= $project->getId() ?>_<?= $day['date'] ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Ajouter des heures</h3>
                                <button class="modal-close"><i class='bx bx-x'></i></button>
                            </div>
                            <form method="post" action="#">
                                <input type="hidden" name="command" value="create">
                                <input type="hidden" name="project_id" value="<?= $project->getId() ?>">
                                <input type="hidden" name="work_date" value="<?= $day['date'] ?>">
                                <div class="modal-body">
                                    <div class="input-container">
                                        <label for="work_count_<?= $project->getId() ?>_<?= $day['date'] ?>">Heures prévues :</label>
                                        <input type="number"
                                               name="work_count"
                                               id="work_count_<?= $project->getId() ?>_<?= $day['date'] ?>"
                                               min="0.5" step="0.5" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="button success">Ajouter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php endif; ?>

            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

</div>