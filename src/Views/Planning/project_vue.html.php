<div id="project-planning">

    <!-- Navigation semaine -->
    <div class="header">
        <h2 class="calendar-title"><?= $year ?></h2>
        <nav class="calendar-nav">
            <button title="Semaine précédente" id="prev-week" class="pagination-link calendar-button"
                data-week="<?= $weeks['previous']['week'] ?>"
                data-year="<?= $weeks['previous']['year'] ?>">
                <i class='bx bxs-chevron-left'></i>
            </button>
            <div class="week-label">
                <strong>N° <?= $weeks['current']['number'] ?></strong>
                <span><?= $weeks['current']['range'] ?></span>
            </div>
            <button title="Semaine suivante" id="next-week" class="pagination-link calendar-button"
                data-week="<?= $weeks['next']['week'] ?>"
                data-year="<?= $weeks['next']['year'] ?>">
                <i class='bx bxs-chevron-right'></i>
            </button>
            <?php if ($weeks['current']['number'] !== $weeks['today']['number'] || $year !== $weeks['today']['year']): ?>
                <button title="Semaine en cours" id="today-week" class="calendar-button"
                    data-week="<?= $weeks['today']['week'] ?>"
                    data-year="<?= $weeks['today']['year'] ?>">
                    Semaine en cours
                </button>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Tableau projets × jours -->
    <table id="planning-table">
        <thead>
            <tr>
                <th>Projets</th>
                <?php foreach ($week_days as $day): ?>
                    <th><?= $day['label'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td class="project-cell">
                        <span class="project-name"><?= $project->getName() ?></span>
                        <span class="project-devs"><?= $project->getDevCount() ?> développeurs</span>
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
                        <?php $entry = $planning_data[$project->getId()][$day['key']] ?? null; ?>
                        <td class="day-cell <?= $entry ? 'has-entry' : '' ?>">
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
                                <a href="#" class="btn-add" data-modal="create_<?= $project->getId() ?>_<?= $day['key'] ?>">
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

            <!-- Modal read projet -->
            <?php require __DIR__ . '/../../partials/modals/read/_top.html.php'; ?>
            <p><strong>Projet :</strong> <?= $project->getName() ?></p>
            <p><strong>Développeurs :</strong> <?= $project->getDevCount() ?></p>
            <?php require __DIR__ . '/../../partials/modals/read/_bottom.html.php'; ?>

            <!-- Modal delete projet -->
            <?php require __DIR__ . '/../../partials/modals/delete/_top.html.php'; ?>
            <input type="hidden" name="project_id" value="<?= $project->getId() ?>">
            <?php require __DIR__ . '/../../partials/modals/delete/_bottom.html.php'; ?>

            <?php foreach ($week_days as $day): ?>
                <?php $entry = $planning_data[$project->getId()][$day['key']] ?? null; ?>

                <?php if ($entry): ?>

                    <!-- Modal edit entrée -->
                    <?php require __DIR__ . '/../../partials/modals/edit/_top.html.php'; ?>
                    <div class="input-container">
                        <label for="work_count">Heures prévues :</label>
                        <input type="number" name="work_count" id="work_count"
                            min="0.5" step="0.5"
                            value="<?= $entry->getCount() ?>" required>
                    </div>
                    <input type="hidden" name="work_id" value="<?= $entry->getId() ?>">
                    <?php require __DIR__ . '/../../partials/modals/edit/_bottom.html.php'; ?>

                    <!-- Modal delete entrée -->
                    <?php require __DIR__ . '/../../partials/modals/delete/_top.html.php'; ?>
                    <input type="hidden" name="work_id" value="<?= $entry->getId() ?>">
                    <?php require __DIR__ . '/../../partials/modals/delete/_bottom.html.php'; ?>

                <?php else: ?>

                    <!-- Modal create entrée (bouton +) -->
                    <!-- TODO: action="..." à renseigner quand la route sera créée -->
                    <div class="modal" id="modal_create_<?= $project->getId() ?>_<?= $day['key'] ?>">
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
                                        <label for="work_count">Heures prévues :</label>
                                        <input type="number" name="work_count" id="work_count"
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