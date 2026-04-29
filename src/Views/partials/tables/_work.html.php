<?php

use Src\Models\Enums\Status\WorkStatus;

?>
<div data-update="/update/heures" data-delete="/delete/heures" data-validate="/validate/heures">
    <form action="">
        <div class="top-bar">
            <div class="filter-container">

                <!-- Search -->
                <div class="input-container">
                    <label for="search">Rechercher par utilisateur :</label>
                    <input type="text" id="search" name="search" placeholder="Ex : John"
                        value="<?= htmlspecialchars($search ?? '', ENT_QUOTES); ?>"
                        list="users-suggestions"
                        autocomplete="off"
                        oninput="clearTimeout(this._t); this._t = setTimeout(() => this.form.submit(), 400)">
                    <datalist id="users-suggestions">
                        <?php
                        $seen = [];
                        foreach ($groupedData as $group):
                            $name = $group['user']->getName();
                            if (!in_array($name, $seen)):
                                $seen[] = $name;
                        ?>
                            <option value="<?= htmlspecialchars($name, ENT_QUOTES); ?>">
                        <?php
                            endif;
                        endforeach; ?>
                    </datalist>
                </div>

                <div class="input-container">
                    <label for="project_id">Projets :</label>
                    <select name="project_id" onchange="this.form.submit()">
                        <option value="">-- Tous les projets --</option>
                        <?php foreach ($projects as $id => $name): ?>
                            <option value="<?= $id ?>" <?= (isset($_GET['project_id']) && $_GET['project_id'] == $id) ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Statut -->
                <div class="input-container">
                    <label for="status">Statut :</label>
                    <select id="status" name="status" onchange="this.form.submit()">
                        <option value="">-- Tous les status --</option>
                        <?php foreach (WorkStatus::getValues() as $statusVal): ?>
                            <option value="<?= $statusVal ?>" <?= ($status ?? '') === $statusVal ? 'selected' : '' ?>>
                                <?= WorkStatus::getLabel($statusVal) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Semaine -->
                <div class="input-container">
                    <label for="week">Semaine :</label>
                    <input type="number" id="week" name="week" min="1" max="53" placeholder="Ex : 12"
                        value="<?= htmlspecialchars($week ?? '', ENT_QUOTES); ?>" onchange="this.form.submit()">
                </div>

                <!-- Année -->
                <div class="input-container">
                    <label for="year">Année :</label>
                    <input type="number" id="year" name="year" min="2000" placeholder="Ex : 2025"
                        value="<?= htmlspecialchars($year ?? '', ENT_QUOTES); ?>" onchange="this.form.submit()">
                </div>

                <!-- Bouton -->
                <button type="submit" class="button primary">Filtrer</button>
                <a href="?">Réinitialiser</a>
            </div>
        </div>
    </form>
    <table class="table-gen">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Nombre d'heures</th>
                <th>Projet</th>
                <th>État</th>
                <th>Semaine</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($groupedData)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2em;">
                        Aucun résultat trouvé.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($groupedData as $groupId => $group): ?>
                    <tr>
                        <td><?= htmlspecialchars($group['user']->getName()) ?></td>
                        <td>
                            <?php
                            $totalHours = 0;
                            foreach ($group['works'] as $work) {
                                $totalHours += $work->getCount();
                            }
                            echo $totalHours . 'h';
                            ?>
                        </td>
                        <td>
                            <?php
                            $projectNames = [];
                            foreach ($group['works_by_project'] as $proj) {
                                $projectNames[] = htmlspecialchars($proj['project']->getName() . ' (' . $proj['client']->getName() . ')');
                            }
                            echo implode(', ', array_unique($projectNames));
                            ?>
                        </td>

                        <td>
                            <?php $lastWork = end($group['works']); ?>
                            <div class="tag <?= WorkStatus::getColor($lastWork->getStatus()) ?>">
                                <?= $lastWork->getStatus(fr: true); ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($group['week']); ?></td>
                        <?php $id = $group['user']->getId() . '_' . $group['week']; ?>
                        <td class="action">
                            <a href="#" class="button primary minimal" data-modal="read_<?= $id ?>"><i class='bx bx-show'></i> <span>Détails</span></a>
                            <a href="#" class="button secondary minimal" data-modal="edit_<?= $id ?>"><i class='bx bx-edit'></i> <span>Modifier</span></a>
                            <a href="#" class="button danger minimal" data-modal="delete_<?= $id ?>"><i class='bx bx-trash'></i> <span>Supprimer</span></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php require __DIR__ . '/_pagination.html.php'; ?>
    <div class="modals">
        <?php foreach ($groupedData as $groupId => $group): ?>
            <?php $id = $group['user']->getId() . '_' . $group['week']; ?>
            <?php
            require __DIR__ . '/../modals/read/_top.html.php';
            ?>
            <p><strong>Utilisateur :</strong> <?= htmlspecialchars($group['user']->getName()) ?></p>
            <p><strong>Semaine :</strong> <?= htmlspecialchars($group['week']) ?></p>

            <div class="hours-details">
                <p><strong>Heures semaine :</strong>
                    <?php
                    $totalHours = 0;
                    foreach ($group['works'] as $work) {
                        $totalHours += $work->getCount();
                    }
                    echo $totalHours . 'h';
                    ?>
                </p>
                <div class="container-hours">
                    <?php foreach ($group['works'] as $work): ?>
                        <div class="content-hours">
                            <p><?= htmlspecialchars($work->getDate()) ?> - <?= $work->getCount() ?>h</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="container-slider">
                <div class="slider-content">
                    <?php
                    $slideIndex = 1;
                    foreach ($group['works_by_project'] as $project) {
                        foreach ($project['entries'] as $entry):
                    ?>
                            <div class="slide-content" id="slide_<?= $slideIndex++ ?>">
                                <h2><?= htmlspecialchars($project['project']->getName()) ?> - <?= htmlspecialchars($project['client']->getName()) ?></h2>
                                <p><strong><?= htmlspecialchars($entry->getDate()) ?> :</strong></p>
                                <p><?= !empty($entry->getDescription()) ? htmlspecialchars($entry->getDescription()) : 'Aucune description' ?></p>
                            </div>
                    <?php
                        endforeach;
                    }
                    ?>
                </div>
                <div class="arrow prev"><i class='bx bx-chevron-left'></i></div>
                <div class="arrow next"><i class='bx bx-chevron-right'></i></div>
                <div class="slide-counter"></div>
            </div>

            <p><strong>État :</strong> <?= $lastWork->getStatus(fr: true); ?></p>
            <?php require __DIR__ . '/../modals/read/_bottom.html.php'; ?>

            <?php
            $workForEdit = reset($group['works']);
            require __DIR__ . '/../modals/edit/_top.html.php';
            ?>
            <div class="input-container">
                <label for="work_count_<?= $id ?>">Nombre d'heures:</label>
                <input type="number" id="work_count_<?= $id ?>" name="work_count" min="0.5" max="35" step="0.5"
                    value="<?= $workForEdit->getCount(); ?>" required>
            </div>
            <div class="input-container">
                <label for="work_description_<?= $id ?>">Description:</label>
                <textarea id="work_description_<?= $id ?>" name="work_description" rows="4" maxlength="250"
                    placeholder="Décrivez ce que vous avez effectué (250 caractères max)" style="resize: none;"><?= htmlspecialchars($workForEdit->getDescription()); ?></textarea>
            </div>
            <div class="input-container">
                <label for="work_status_<?= $id ?>">État:</label>
                <select id="work_status_<?= $id ?>" name="work_status">
                    <?php foreach (WorkStatus::getEnumOptions() as $status => $label): ?>
                        <option value="<?= $status; ?>" <?= $workForEdit->getStatus() == $status ? 'selected' : ''; ?>>
                            <?= $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="work_id" value="<?= $workForEdit->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
            <?php require __DIR__ . '/../modals/edit/_bottom.html.php'; ?>

            <?php
            require __DIR__ . '/../modals/delete/_top.html.php';
            ?>
            <input type="hidden" name="work_id" value="<?= $workForEdit->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
            <?php require __DIR__ . '/../modals/delete/_bottom.html.php'; ?>
        <?php endforeach; ?>
    </div>
</div>