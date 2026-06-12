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

<div id="project-planning" data-create="/previsionnel" data-update="/previsionnel" data-delete="/previsionnel">

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

    <!-- Filtre des projets affichés -->
    <details class="project-filter">
        <summary>
            Filtrer les projets affichés (<?= count($projects) ?>/<?= count($available_projects) ?>)
        </summary>
        <form method="get" class="project-filter-form">
            <input type="hidden" name="view" value="<?= $current_view ?>">
            <input type="hidden" name="month" value="<?= $months['current']['month'] ?>">
            <input type="hidden" name="year" value="<?= $months['current']['year'] ?>">
            <input type="hidden" name="week" value="<?= $current_week['number'] ?>">

            <div class="project-filter-actions">
                <button type="button" class="button secondary minimal" data-filter-select-all>Tout cocher</button>
                <button type="button" class="button secondary minimal" data-filter-select-none>Tout décocher</button>
            </div>

            <div class="project-filter-list">
                <?php foreach ($available_projects as $project): ?>
                    <label class="project-filter-item">
                        <input type="checkbox" name="projects[]" value="<?= $project->getId() ?>"
                                <?= in_array($project->getId(true), $selected_project_ids, true) ? 'checked' : '' ?>>
                        <?= $project->getName() ?>
                    </label>
                <?php endforeach; ?>
                <?php if (empty($available_projects)): ?>
                    <p>Aucun projet en cours ou en attente.</p>
                <?php endif; ?>
            </div>

            <button type="submit" class="button primary">Appliquer</button>
        </form>
    </details>

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
                        <a href="#" class="button primary" data-modal="read_project_<?= $project->getId() ?>">
                            <i class='bx bx-show'></i>
                        </a>
                    </div>
                </td>
                <?php foreach ($week_days as $day): ?>
                    <?php $entry = $planning_data_by_project[$project->getId(true)][$day['date']] ?? null; ?>
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

            <!-- Modal détails projet -->
            <div id="read_project_<?= $project->getId() ?>" class="modal">
                <div class="modal-content">
                    <h2>Détails</h2>
                    <div class="modal-body">
                        <p><strong>Projet :</strong> <?= $project->getName() ?></p>
                        <p><strong>Développeurs :</strong> <?= $project->getResource() ?></p>
                    </div>
                    <div class="modal-footer">
                        <button class="close-modal">Fermer</button>
                    </div>
                </div>
            </div>

            <?php foreach ($week_days as $day): ?>
                <?php $entry = $planning_data_by_project[$project->getId(true)][$day['date']] ?? null; ?>

                <?php if ($entry): ?>
                    <?php $workId = $entry->getId(); ?>

                    <!-- Modal édition d'un créneau -->
                    <div id="edit_<?= $workId ?>" class="modal">
                        <form id="edit-form-<?= $workId ?>" method="post">
                            <div class="modal-content">
                                <h2>Modifier - <?= $day['display'] ?? $day['date'] ?></h2>
                                <div class="modal-body">
                                    <input type="hidden" name="command" value="update">
                                    <input type="hidden" name="work_id" value="<?= $workId ?>">
                                    <input type="hidden" name="current_month" value="<?= $months['current']['month'] ?>">
                                    <input type="hidden" name="current_year" value="<?= $months['current']['year'] ?>">
                                    <input type="hidden" name="current_view" value="<?= $current_view ?>">
                                    <input type="hidden" name="current_week" value="<?= $current_week['number'] ?>">

                                    <p><strong>Projet :</strong> <?= $project->getName() ?></p>

                                    <div class="input-container">
                                        <label for="work_count_<?= $workId ?>">Heures prévues :</label>
                                        <input type="number"
                                               name="work_count"
                                               id="work_count_<?= $workId ?>"
                                               min="0.5" step="0.5"
                                               value="<?= $entry->getCount(raw: true) ?>" required>
                                    </div>
                                </div>
                                <?php \Src\Services\CsrfService::insertToken(); ?>
                                <div class="modal-footer">
                                    <button type="submit" class="button success update-save">Sauvegarder</button>
                                    <button type="button" class="close-modal">Fermer</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal suppression d'un créneau -->
                    <div id="delete_<?= $workId ?>" class="modal">
                        <div class="modal-content">
                            <h2>Confirmer la suppression</h2>
                            <p>Êtes-vous sûr de vouloir supprimer ce créneau ?</p>
                            <div class="modal-footer">
                                <button class="close-modal">Annuler</button>
                                <form id="delete-form-<?= $workId ?>" method="POST">
                                    <?php \Src\Services\CsrfService::insertToken(); ?>
                                    <input type="hidden" name="command" value="delete">
                                    <input type="hidden" name="work_id" value="<?= $workId ?>">
                                    <input type="hidden" name="current_month" value="<?= $months['current']['month'] ?>">
                                    <input type="hidden" name="current_year" value="<?= $months['current']['year'] ?>">
                                    <input type="hidden" name="current_view" value="<?= $current_view ?>">
                                    <input type="hidden" name="current_week" value="<?= $current_week['number'] ?>">
                                    <button class="confirm-delete">Oui</button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php else: ?>

                    <!-- Modal création d'un créneau -->
                    <div id="create_<?= $project->getId() ?>_<?= $day['date'] ?>" class="modal">
                        <form id="create-form-<?= $project->getId() ?>_<?= $day['date'] ?>" method="post">
                            <div class="modal-content">
                                <h2>Ajouter un créneau - <?= $day['display'] ?? $day['date'] ?></h2>
                                <div class="modal-body">
                                    <input type="hidden" name="command" value="create">
                                    <input type="hidden" name="work_project" value="<?= $project->getId() ?>">
                                    <input type="hidden" name="work_date" value="<?= $day['date'] ?>">
                                    <input type="hidden" name="current_month" value="<?= $months['current']['month'] ?>">
                                    <input type="hidden" name="current_year" value="<?= $months['current']['year'] ?>">
                                    <input type="hidden" name="current_view" value="<?= $current_view ?>">
                                    <input type="hidden" name="current_week" value="<?= $current_week['number'] ?>">

                                    <p><strong>Projet :</strong> <?= $project->getName() ?></p>

                                    <div class="input-container">
                                        <label for="work_user_<?= $project->getId() ?>_<?= $day['date'] ?>">Collaborateur :</label>
                                        <select id="work_user_<?= $project->getId() ?>_<?= $day['date'] ?>" name="work_user" required>
                                            <option value="" disabled selected>Choisir un collaborateur</option>
                                            <?php foreach ($users as $u): ?>
                                                <?php if ($u->getId(true) === 0) continue; ?>
                                                <option value="<?= $u->getId() ?>"><?= $u->getName() ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="input-container">
                                        <label for="work_count_<?= $project->getId() ?>_<?= $day['date'] ?>">Heures prévues :</label>
                                        <input type="number"
                                               name="work_count"
                                               id="work_count_<?= $project->getId() ?>_<?= $day['date'] ?>"
                                               min="0.5" step="0.5" required>
                                    </div>
                                </div>
                                <?php \Src\Services\CsrfService::insertToken(); ?>
                                <div class="modal-footer">
                                    <button type="submit" class="button success confirm-create">Ajouter</button>
                                    <button type="button" class="close-modal">Fermer</button>
                                </div>
                            </div>
                        </form>
                    </div>

                <?php endif; ?>

            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

</div>

<script>
    document.querySelectorAll('.project-filter-form [data-filter-select-all]').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.project-filter-form').querySelectorAll('input[name="projects[]"]').forEach(cb => cb.checked = true);
        });
    });
    document.querySelectorAll('.project-filter-form [data-filter-select-none]').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.project-filter-form').querySelectorAll('input[name="projects[]"]').forEach(cb => cb.checked = false);
        });
    });
</script>