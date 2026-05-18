<?php

use Src\Models\Enums\Status\ProjectStatus;

?>
<div data-update="/update/projets" data-delete="/delete/projets" data-create="/projets"
     data-assign="/assign/projets" data-unassign="/unassign/projets">
    <form action="">
        <div class="top-bar">
            <div class="filter-container">

                <!-- Search -->
                <div class="input-container">
                    <label for="search">Rechercher par nom de projet :</label>
                    <input type="text" id="search" name="search" placeholder="Ex : Application de gestion santé"
                        value="<?= htmlspecialchars($search ?? '', ENT_QUOTES); ?>"
                        list="projects-suggestions"
                        autocomplete="off"
                        oninput="clearTimeout(this._t); this._t = setTimeout(() => this.form.submit(), 400)">
                    <datalist id="projects-suggestions">
                        <?php
                        $seen = [];
                        foreach ($data as $row_data):
                            $name = $row_data['project']->getName();
                            if (!in_array($name, $seen)):
                                $seen[] = $name;
                        ?>
                            <option value="<?= htmlspecialchars($name, ENT_QUOTES); ?>">
                        <?php
                            endif;
                        endforeach; ?>
                    </datalist>
                </div>

                <!-- Statut -->
                <div class="input-container">
                    <label for="status">Statut :</label>
                    <?php $currentStatus = $_GET['status'] ?? ''; ?>
                    <select id="status" name="status" onchange="this.form.submit()">
                        <option value="">-- Tous les statuts --</option>
                        <?php foreach (ProjectStatus::getEnumOptions() as $status => $label): ?>
                            <option value="<?= $status; ?>" <?= $currentStatus === $status ? 'selected' : ''; ?>>
                                <?= $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Année de début -->
                <div class="input-container">
                    <label for="start_year">Année de début:</label>
                    <input type="number" id="start_year" name="start_year" placeholder="Ex: 2025" min="2000" max="2250" step="1"
                        value="<?= htmlspecialchars($start_year ?? '', ENT_QUOTES); ?>" onchange="this.form.submit()">
                </div>

                <!-- Bouton -->
                <button type="submit" class="button primary">Filtrer</button>
                <a href="?">Réinitialiser</a>

                <div class="container-btn-create">
                    <a href="#" data-modal="create">Créer</a>
                </div>
            </div>
        </div>
    </form>

    <table class="table-gen">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Volume d'heures</th>
                <th>Ressources humaines</th>
                <th>État</th>
                <th>Progression</th>
                <th class="action">
                    <p>Action</p>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 2em;">
                        Aucun résultat trouvé.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($data as $id => $row_data): ?>
                    <tr>
                        <td><?= $row_data['project']->getName(); ?></td>
                        <td><?= $row_data['project']->getResource(); ?></td>
                        <td><?= count($row_data['users']); ?> développeur<?= count($row_data['users']) > 1 ? 's' : ''; ?></td>
                        <td>
                            <div class="tag <?= ProjectStatus::getColor($row_data['project']->getStatus()) ?>">
                                <?= $row_data['project']->getStatus(fr: true); ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $progress = (int) $row_data['progression']['progression'];
                            $color = '#28a745';
                            ?>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?= $progress ?>%; background-color: <?= $color ?>;"></div>
                            </div>
                            <small><?= $progress ?>%</small><br>
                            <small><?= $row_data['progression']['workedHours'] ?>h / <?= $row_data['progression']['resourceHours'] ?>h</small>
                        </td>
                        <td class="action">
                            <a href="#" class="button primary minimal" data-modal="read_<?= $id ?>"><i class='bx bx-show'></i>
                                <span>Détails</span></a>
                            <a href="#" class="button secondary minimal" data-modal="edit_<?= $id ?>"><i class='bx bx-edit'></i>
                                <span>Modifier</span></a>
                            <a href="#" class="button danger minimal" data-modal="delete_<?= $id ?>"><i class='bx bx-trash'></i>
                                <span>Supprimer</span></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php require __DIR__ . '/_pagination.html.php'; ?>

    <div class="modals">
        <?php foreach ($data as $id => $row_data):
            $assignedUserIds = array_column($row_data['users'], 'user_id');
        ?>

            <?php require __DIR__ . '/../modals/read/_top.html.php'; ?>
            <div class="project-stats-summary">
            <?php
                $progress = (int) $row_data['progression']['progression'];
                $worked   = $row_data['progression']['workedHours'];
                $total    = $row_data['progression']['resourceHours'];
                $color    = '#28a745';
            ?>
                <div>
                    <h3 class="summary-title">Volume d'heures</h3>
                    <p class="summary-content"><?= $row_data['project']->getResource(); ?>h</p>
                </div>
                <div>
                    <h3 class="summary-title">Progression</h3>
                    <p class="summary-content"><?= $progress ?>%</p>
                </div>
                <div>
                    <h3 class="summary-title">Heures réalisées</h3>
                    <p class="summary-content"><?= $worked ?>h</p>
                </div>
                <div>
                    <h3 class="summary-title">État</h3>
                    <p class="tag <?= ProjectStatus::getColor($row_data['project']->getStatus()) ?> summary-content">
                        <?= $row_data['project']->getStatus(fr: true);?>
                    </p>
                </div>
            </div>

            <!-- Ressources humaines — modale lecture -->
            <div>
                <h3 class="human-ressources"><span class="bx bx-group"></span>Ressources humaines</h3>
                <div class="assigned">
                    <h4 class="assigned-title">
                        <span class="bx bx-check"></span>
                        Assignés (<?= count($row_data['users']); ?>)
                    </h4>
                    <?php if (empty($row_data['users'])): ?>
                        <p>Aucun développeur assigné.</p>
                    <?php else: ?>
                        <ul class="user-list">
                            <?php foreach ($row_data['users'] as $u): ?>
                                <li class="user-item">
                                    <?php if (!empty($u['user_picture'])): ?>
                                        <img src="<?= htmlspecialchars($u['user_picture']); ?>"
                                             alt="<?= htmlspecialchars($u['user_firstname'] . ' ' . $u['user_lastname']); ?>"
                                             class="user-avatar">
                                    <?php endif; ?>
                                    <span class="user-name">
                                        <?= htmlspecialchars($u['user_firstname'] . ' ' . $u['user_lastname']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <p><strong>Nom :</strong> <?= $row_data['project']->getName(); ?></p>
            <p><strong>Client :</strong> <?= $row_data['client']->getName(); ?></p>
            <p><strong>Description:</strong>
                <?= !empty($row_data['project']->getDescription()) ? $row_data['project']->getDescription() : 'Non renseignée'; ?>
            </p>
            <p><strong>Progression :</strong></p>
            <div class="progress-bar-container" style="margin-bottom: 0.25rem;">
                <div class="progress-bar" style="width: <?= $progress ?>%; background-color: <?= $color ?>;"></div>
            </div>
            <small><?= $progress ?>%</small><br>
            <small><?= $worked ?>h / <?= $total ?>h</small>
            <p><strong>Départ prévu :</strong> <?= $row_data['project']->getStart(); ?></p>
            <p><strong>Fin prévue :</strong> <?= $row_data['project']->getEnd(); ?></p>
            <p><strong>Fin réelle :</strong> <?= $row_data['project']->getRealEnd() ?: 'Non renseignée'; ?></p>
            <p><strong>Créé le :</strong> <?= $row_data['project']->getCreation(); ?></p>
            <?php require __DIR__ . '/../modals/read/_bottom.html.php'; ?>


            <?php require __DIR__ . '/../modals/edit/_top.html.php'; ?>

            <!-- Ressources humaines — modale édition -->
            <div>
                <h3 class="human-ressources"><span class="bx bx-group"></span>Ressources humaines</h3>
                <div class="human-ressources-section">

                    <!-- Colonne : développeurs assignés -->
                    <div class="assigned">
                        <h4 class="assigned-title">
                            <span class="bx bx-user-minus"></span>
                            Assignés (<?= count($row_data['users']); ?>)
                        </h4>
                        <?php if (empty($row_data['users'])): ?>
                            <p>Aucun développeur assigné.</p>
                        <?php else: ?>
                            <ul class="user-list">
                                <?php foreach ($row_data['users'] as $u): ?>
                                    <li class="user-item">
                                        <?php if (!empty($u['user_picture'])): ?>
                                            <img src="<?= htmlspecialchars($u['user_picture']); ?>"
                                                 alt="<?= htmlspecialchars($u['user_firstname'] . ' ' . $u['user_lastname']); ?>"
                                                 class="user-avatar">
                                        <?php endif; ?>
                                        <span class="user-name">
                                            <?= htmlspecialchars($u['user_firstname'] . ' ' . $u['user_lastname']); ?>
                                        </span>
                                        <!-- Bouton retirer : envoie un appel AJAX via data-unassign -->
                                        <button type="button"
                                                class="button danger minimal btn-unassign"
                                                data-project-id="<?= $row_data['project']->getId(); ?>"
                                                data-user-id="<?= (int)$u['user_id']; ?>"
                                                title="Retirer du projet">
                                            <i class="bx bx-user-minus"></i>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Colonne : développeurs disponibles -->
                    <div class="available">
                        <h4 class="available-title">
                            <span class="bx bx-user-plus"></span>
                            Disponibles (<?= count($all_users) - count($row_data['users']); ?>)
                        </h4>
                        <ul class="user-list">
                            <?php foreach ($all_users as $uid => $user):
                                if (in_array($uid, $assignedUserIds)) continue; ?>
                                <li class="user-item">
                                    <?php if (!empty($user->getPicture())): ?>
                                        <img src="<?= htmlspecialchars($user->getPicture()); ?>"
                                             alt="<?= htmlspecialchars($user->getFirstname() . ' ' . $user->getLastname()); ?>"
                                             class="user-avatar">
                                    <?php endif; ?>
                                    <span class="user-name">
                                        <?= htmlspecialchars($user->getFirstname() . ' ' . $user->getLastname()); ?>
                                    </span>
                                    <!-- Bouton assigner : envoie un appel AJAX via data-assign -->
                                    <button type="button"
                                            class="button primary minimal btn-assign"
                                            data-project-id="<?= $row_data['project']->getId(); ?>"
                                            data-user-id="<?= $uid; ?>"
                                            title="Assigner au projet">
                                        <i class="bx bx-user-plus"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>
            </div>

            <div class="input-container">
                <label for="project_name">Nom :</label>
                <input type="text" id="project_name" name="project_name" value="<?= $row_data['project']->getName(); ?>" required>
            </div>
            <div class="input-container">
                <label for="project_description">Description :</label>
                <textarea id="project_description" name="project_description" rows="4" maxlength="250"
                    placeholder="Décrire le projet (250 caractères max)"
                    style="resize: none;"><?= $row_data['project']->getDescription(); ?></textarea>
            </div>
            <div class="input-container">
                <label for="project_resource">Volume d'heures :</label>
                <input type="number" id="project_resource" name="project_resource" min="0" step="0.5"
                    value="<?= $row_data['project']->getResource(); ?>">
            </div>
            <div class="input-container">
                <label for="project_start">Départ de départ prévue le:</label>
                <input type="date" id="project_start" name="project_start" value="<?= $row_data['project']->getStart(); ?>">
            </div>
            <div class="input-container">
                <label for="project_end">Fin prévue le :</label>
                <input type="date" id="project_end" name="project_end" value="<?= $row_data['project']->getEnd(); ?>" disabled>
                <label for="project_realend">Fin réelle :</label>
                <input type="date" id="project_realend" name="project_realend" value="<?= $row_data['project']->getRealEnd(); ?>">
            </div>
            <div class="input-container">
                <label for="project_status">État :</label>
                <select id="project_status" name="project_status">
                    <?php foreach (ProjectStatus::getEnumOptions() as $status => $label): ?>
                        <option value="<?= $status; ?>" <?= $row_data['project']->getStatus() == $status ? 'selected' : ''; ?>>
                            <?= $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="project_id" value="<?= $row_data['project']->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
            <?php require __DIR__ . '/../modals/edit/_bottom.html.php'; ?>


            <?php require __DIR__ . '/../modals/delete/_top.html.php'; ?>
            <input type="hidden" name="project_id" value="<?= $row_data['project']->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
            <?php require __DIR__ . '/../modals/delete/_bottom.html.php'; ?>

        <?php endforeach; ?>

        <?php require __DIR__ . '/../modals/create/_top.html.php'; ?>
        <div class="input-container">
            <label for="project_name">Nom du projet :</label>
            <input type="text" id="project_name" name="project_name" required>
        </div>
        <div class="input-container">
            <label for="project_description">Description :</label>
            <textarea id="project_description" name="project_description" rows="4" maxlength="250"
                placeholder="Décrire le projet (250 caractères max)" style="resize: none;"></textarea>
        </div>
        <div class="input-container">
            <label for="project_resource">Temps prévu (en heures travaillées) :</label>
            <input type="number" id="project_resource" name="project_resource" min="0" step="0.5" value="0">
        </div>
        <div class="input-container">
            <label for="project_start">Départ prévue le :</label>
            <input type="date" id="project_start" name="project_start">
        </div>
        <div class="input-container">
            <label for="project_end">Fin prévue le :</label>
            <input type="date" id="project_end" name="project_end">
        </div>
        <div class="input-container">
            <label for="project_client">Client :</label>
            <select id="project_client" name="project_client">
                <option value="" disabled selected>Choisir un client</option>
                <option value="null">Aucun client</option>
                <optgroup label="Clients">
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client->getId(); ?>"><?= $client->getName(); ?></option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>
        <p class="text-muted">
            <span class="bx bx-info-circle"></span>
            Les développeurs pourront être assignés après la création du projet.
        </p>
        <?php require __DIR__ . '/../modals/create/_bottom.html.php'; ?>
    </div>
</div>