<?php

use Src\Services\CsrfService;

?>
<div id="planning-calendar" data-create="/previsionnel" data-update="/previsionnel" data-delete="/previsionnel">
    <div id="calendar-container">
        <div id="calendar">
            <div class="header">
                <nav class="calendar-nav">
                    <a title="Mois précédent" class="pagination-link calendar-button"
                       href="?view=calendar&month=<?= $months['previous']['month']; ?>&year=<?= $months['previous']['year']; ?>">
                        <i class='bx bxs-chevron-left'></i>
                    </a>
                    <a title="Mois suivant" class="pagination-link calendar-button"
                       href="?view=calendar&month=<?= $months['next']['month']; ?>&year=<?= $months['next']['year']; ?>">
                        <i class='bx bxs-chevron-right'></i>
                    </a>

                    <?php if (
                            $months['current']['year'] !== $months['today']['year'] ||
                            $months['current']['month'] !== $months['today']['month']
                    ): ?>
                        <a title="Mois en cours" class="calendar-button"
                           href="?view=calendar&month=<?= $months['today']['month']; ?>&year=<?= $months['today']['year']; ?>">
                            Mois en cours
                        </a>
                    <?php endif; ?>
                </nav>
                <h2 id="calendar-title" class="calendar-title">
                    <?= $months['current']['name']; ?> <?= $months['current']['year']; ?>
                </h2>
            </div>

            <div class="planning-week-list">
                <?php foreach ($weeks as $week): ?>
                    <section class="planning-week">
                        <header class="planning-week-header">
                            <div>
                                <span class="planning-week-kicker">Semaine</span>
                                <h3>N° <?= $week['number']; ?></h3>
                            </div>
                            <span class="planning-week-range"><?= $week['range']; ?></span>
                        </header>

                        <div class="planning-days-grid">
                            <?php foreach ($week['days'] as $day): ?>
                                <article class="planning-day <?= !$day['is_current_month'] ? 'is-outside-month' : ''; ?> <?= $day['is_today'] ? 'is-today' : ''; ?>">
                                    <div class="planning-day-header">
                                        <span class="planning-day-name"><?= $day['name']; ?></span>
                                        <span class="planning-day-number"><?= $day['number']; ?></span>
                                    </div>
                                    <time datetime="<?= $day['date']; ?>"><?= $day['display']; ?></time>
                                    <div class="planning-day-body">
                                        <?php $entries = $planning_data[$day['date']] ?? []; ?>
                                        <?php if (empty($entries)): ?>
                                            <span class="planning-day-empty">Aucune prévision</span>
                                        <?php else: ?>
                                            <?php foreach ($entries as $userId => $entry): ?>
                                                <div class="hour-card <?= $entry['work']->getStatus(); ?>">
                                                    <p>
                                                        <?= htmlspecialchars($entry['project_name']); ?>
                                                        - <span><?= $entry['work']->getCount(); ?>h</span>
                                                    </p>
                                                    <p class="hour-card-user">
                                                        <?= htmlspecialchars($entry['user_firstname'] . ' ' . $entry['user_lastname']); ?>
                                                    </p>
                                                    <div class="hour-card-actions">
                                                        <a href="#" class="button contrast" data-modal="edit_<?= $entry['work']->getId(); ?>">
                                                            <i class='bx bx-edit'></i>
                                                        </a>
                                                        <a href="#" class="button danger" data-modal="delete_<?= $entry['work']->getId(); ?>">
                                                            <i class='bx bx-trash'></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <a href="#" class="button primary minimal btn-add-planning" data-modal="create_<?= $day['date']; ?>">
                                            <i class='bx bx-plus'></i> Ajouter
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modals">
        <?php foreach ($weeks as $week): ?>
            <?php foreach ($week['days'] as $day): ?>

                <!-- Modal création d'un créneau pour ce jour -->
                <div id="create_<?= $day['date']; ?>" class="modal">
                    <form id="create-form-<?= $day['date']; ?>" method="post">
                        <div class="modal-content">
                            <h2>Ajouter un créneau - <?= $day['display']; ?></h2>
                            <div class="modal-body">
                                <input type="hidden" name="command" value="create">
                                <input type="hidden" name="work_date" value="<?= $day['date']; ?>">
                                <input type="hidden" name="current_month" value="<?= $months['current']['month']; ?>">
                                <input type="hidden" name="current_year" value="<?= $months['current']['year']; ?>">

                                <div class="input-container">
                                    <label for="work_user_<?= $day['date']; ?>">Collaborateur :</label>
                                    <select id="work_user_<?= $day['date']; ?>" name="work_user" required>
                                        <option value="" disabled selected>Choisir un collaborateur</option>
                                        <?php foreach ($users as $u): ?>
                                            <?php if ($u->getId(true) === 0) continue; ?>
                                            <option value="<?= $u->getId(); ?>"><?= $u->getName(); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="input-container">
                                    <label for="work_project_<?= $day['date']; ?>">Projet :</label>
                                    <select id="work_project_<?= $day['date']; ?>" name="work_project" required>
                                        <option value="" disabled selected>Choisir un projet</option>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?= $project->getId(); ?>"><?= $project->getName(); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="input-container">
                                    <label for="work_count_<?= $day['date']; ?>">Heures prévues :</label>
                                    <input type="number" id="work_count_<?= $day['date']; ?>" name="work_count"
                                           min="0.5" max="7" step="0.5" required>
                                </div>
                            </div>
                            <?php CsrfService::insertToken(); ?>
                            <div class="modal-footer">
                                <button type="submit" class="button success confirm-create">Ajouter</button>
                                <button type="button" class="close-modal">Fermer</button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php
                $entries = $planning_data[$day['date']] ?? [];
                foreach ($entries as $userId => $entry):
                    $work = $entry['work'];
                    $workId = $work->getId();
                    ?>

                    <!-- Modal édition d'un créneau -->
                    <div id="edit_<?= $workId; ?>" class="modal">
                        <form id="edit-form-<?= $workId; ?>" method="post">
                            <div class="modal-content">
                                <h2>Modifier - <?= $day['display']; ?></h2>
                                <div class="modal-body">
                                    <input type="hidden" name="command" value="update">
                                    <input type="hidden" name="work_id" value="<?= $workId; ?>">
                                    <input type="hidden" name="current_month" value="<?= $months['current']['month']; ?>">
                                    <input type="hidden" name="current_year" value="<?= $months['current']['year']; ?>">

                                    <p><strong>Collaborateur :</strong> <?= htmlspecialchars($entry['user_firstname'] . ' ' . $entry['user_lastname']); ?></p>
                                    <p><strong>Projet :</strong> <?= htmlspecialchars($entry['project_name']); ?></p>

                                    <div class="input-container">
                                        <label for="work_count_edit_<?= $workId; ?>">Heures prévues :</label>
                                        <input type="number" id="work_count_edit_<?= $workId; ?>" name="work_count"
                                               min="0.5" max="7" step="0.5" value="<?= $work->getCount(raw: true); ?>" required>
                                    </div>
                                </div>
                                <?php CsrfService::insertToken(); ?>
                                <div class="modal-footer">
                                    <button type="submit" class="button success update-save">Sauvegarder</button>
                                    <button type="button" class="close-modal">Fermer</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal suppression d'un créneau -->
                    <div id="delete_<?= $workId; ?>" class="modal">
                        <div class="modal-content">
                            <h2>Confirmer la suppression</h2>
                            <p>Êtes-vous sûr de vouloir supprimer ce créneau ?</p>
                            <div class="modal-footer">
                                <button class="close-modal">Annuler</button>
                                <form id="delete-form-<?= $workId; ?>" method="POST">
                                    <?php CsrfService::insertToken(); ?>
                                    <input type="hidden" name="command" value="delete">
                                    <input type="hidden" name="work_id" value="<?= $workId; ?>">
                                    <input type="hidden" name="current_month" value="<?= $months['current']['month']; ?>">
                                    <input type="hidden" name="current_year" value="<?= $months['current']['year']; ?>">
                                    <button class="confirm-delete">Oui</button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
