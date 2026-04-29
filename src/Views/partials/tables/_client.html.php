<?php

use Src\Models\Enums\Type\ClientType;

?>

<div data-update="/update/clients" data-delete="/delete/clients" data-create="/clients">
    <form action="">
        <div class="top-bar">
            <div class="filter-container">
                <!-- Recherche -->
                <div class="input-container">
                    <label for="search">Rechercher par client :</label>
                    <input type="text" id="search" name="search" placeholder="Ex : Synapsia"
                        value="<?= htmlspecialchars($search ?? '', ENT_QUOTES); ?>"
                        list="clients-suggestions"
                        autocomplete="off"
                        oninput="clearTimeout(this._t); this._t = setTimeout(() => this.form.submit(), 400)">
                    <datalist id="clients-suggestions">
                        <?php
                        $seen = [];
                        foreach ($data as $row_data):
                            $name = $row_data->getName();
                            if (!in_array($name, $seen)):
                                $seen[] = $name;
                        ?>
                            <option value="<?= htmlspecialchars($name, ENT_QUOTES); ?>">
                        <?php
                            endif;
                        endforeach; ?>
                    </datalist>
                </div>

                <!-- Type de client -->
                <div class="input-container">
                    <label for="type">Type :</label>
                    <select id="type" name="type" onchange="this.form.submit()">
                        <option value="">-- Tous les types --</option>

                        <?php foreach (ClientType::getGroupedEnumOptions() as $groupLabel => $types): ?>
                            <optgroup label="<?= htmlspecialchars($groupLabel, ENT_QUOTES); ?>">
                                <?php foreach ($types as $typeOption): ?>
                                    <option value="<?= $typeOption ?>"
                                        <?= ($type === $typeOption) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($typeOption, ENT_QUOTES); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Bouton -->
                <button type="submit" class="button primary">Filtrer</button>
                <a href="?">Réinitialiser</a>
            </div>

            <div class="container-btn-create">
                <a href="#" class="button secondary" data-modal="create">Créer</a>
            </div>
        </div>
    </form>

    <table class="table-gen">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>Projets</th>
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
                        <td><?= $row_data->getName(); ?></td>
                        <td><?= $row_data->getType(); ?></td>
                        <td>
                            <?php
                            $clientId = $row_data->getId();
                            $projects = $projectsByClient[$clientId] ?? [];
                            ?>

                            <?php if (empty($projects)): ?>
                                <span class="no-project">Aucun projet</span>
                            <?php else: ?>
                                <select class="project-select">
                                    <?php foreach ($projects as $project): ?>
                                        <option><?= htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
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
        <?php foreach ($data as $id => $row_data): ?>
            <?php require __DIR__ . '/../modals/read/_top.html.php'; ?>
            <p><strong>Nom :</strong> <?= $row_data->getName(); ?></p>
            <p><strong>Type :</strong> <?= $row_data->getType(); ?></p>

            <?php
            $clientId = $row_data->getId();
            $projects = $projectsByClient[$clientId] ?? [];
            ?>
            
            <p><strong>Projets :</strong></p>
            <?php if (empty($projects)): ?>
                <p><em>Aucun projet</em></p>
            <?php else: ?>
                <select class="project-select">
                    <?php foreach ($projects as $project): ?>
                        <option><?= htmlspecialchars($project['project_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php require __DIR__ . '/../modals/read/_bottom.html.php'; ?>

            <?php require __DIR__ . '/../modals/edit/_top.html.php'; ?>
            <div class="input-container">
                <label for="client_name">Nom :</label>
                <input type="text" id="client_name" name="client_name" value="<?= $row_data->getName(); ?>"
                    required>
            </div>
            <div class="input-container">
                <label for="client_type">Type :</label>
                <select id="client_type" name="client_type" required>
                    <?php foreach (ClientType::getGroupedEnumOptions() as $group => $options): ?>
                        <optgroup label="<?= htmlspecialchars($group) ?>">
                            <?php foreach ($options as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="client_id" value="<?= $row_data->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
            <?php require __DIR__ . '/../modals/edit/_bottom.html.php'; ?>

            <?php require __DIR__ . '/../modals/delete/_top.html.php'; ?>
            <input type="hidden" name="client_id" value="<?= $row_data->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
        <?php require __DIR__ . '/../modals/delete/_bottom.html.php';
        endforeach; ?>

        <?php require __DIR__ . '/../modals/create/_top.html.php'; ?>
        <div class="input-container">
            <label for="client_name">Nom du client :</label>
            <input type="text" id="client_name" name="client_name" required>
        </div>
        <div class="input-container">
            <label for="client_type">Type du client :</label>
            <select id="client_type" name="client_type" required>
                <?php foreach (ClientType::getGroupedEnumOptions() as $group => $options): ?>
                    <optgroup label="<?= htmlspecialchars($group) ?>">
                        <?php foreach ($options as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
        <?php require __DIR__ . '/../modals/create/_bottom.html.php'; ?>
    </div>
</div>