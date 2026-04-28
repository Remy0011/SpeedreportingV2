<?php

use Src\Services\CsrfService;
use Src\Models\Enums\Status\WorkStatus;

?>
<div data-validate="/heures/valider" id="work_validate">
    <form action="">
        <div class="top-bar">
            <div class="filter-container">
                <div class="input-container">
                    <label for="search">Rechercher :</label>
                    <input type="text" id="search" name="search" placeholder="Rechercher par utilisateur"
                        value="<?= htmlspecialchars($search ?? '', ENT_QUOTES); ?>" 
                        onchange="this.form.submit()">
                </div>

                <div class="input-container">
                    <label for="project_id">Projet :</label>
                    <select name="project_id" onchange="this.form.submit()">
                        <option value="">-- Tous les projets --</option>
                        <?php foreach ($projects as $id => $name): ?>
                            <option value="<?= $id ?>" <?= ($_GET['project_id'] ?? '') == $id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-container">
                    <label for="year">Année :</label>
                    <input type="number" name="year" placeholder="Par année" value="<?= htmlspecialchars($_GET['year'] ?? '') ?>" onchange="this.form.submit()">
                </div>

                <div class="input-container">
                    <label for="week">Semaine :</label>
                    <input type="number" name="week" placeholder="Par semaine" value="<?= htmlspecialchars($_GET['week'] ?? '') ?>" onchange="this.form.submit()">
                </div>

                <button type="submit" class="button primary">Filtrer</button>
                <a href="?">Réinitialiser</a>
            </div>
        </div>
    </form>

    <table class="table-gen">
        <thead>
            <tr>
                <th><input type="checkbox" id="check_all"></th>
                <th>Semaine</th>
                <th>Utilisateur</th>
                <th>Entrées</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan='5' style="text-align:center; padding: 2em;">Aucune donnée à afficher</td>
                </tr>
                <?php else:
                foreach ($data as $id => $row_data): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="row-check" name="checkbox-<?= $id; ?>" data-week-target="<?= $id; ?>">
                            <?php
                            foreach ($row_data['week']->getIds() as $work_id): ?>
                                <input type="hidden" name="work_id[]" value="<?= $work_id; ?>" data-week="<?= $id; ?>">
                            <?php endforeach;
                            ?>
                        </td>
                        <td><?= $row_data['week']->getStartToEnd(year: false); ?></td>
                        <td><?= $row_data['week']->getUser()->getName(); ?></td>
                        <td><?= $row_data['week']->getEntriesCount(); ?></td>
                        <td class="action">
                            <a href="#" class="button success" data-modal="validate_<?= $id ?>">Voir les heures</a>
                        </td>
                    </tr>
            <?php endforeach;
            endif; ?>
        </tbody>
        <form id="validate-multiple-form" class="hidden" method="POST" action="/heures/valider">
            <?php CsrfService::insertToken(); ?>
        </form>
        <button id="validate-multiple" class="button success" disabled>
            Valider la sélection
        </button>
    </table>
    <?php require __DIR__ . '/_pagination.html.php'; ?>
    <div class="modals">
        <?php foreach ($data as $id => $row_data): ?>
            <div id='validate_<?= $id; ?>' class='modal'>
                <div class='modal-content'>
                    <h2>Confirmer la validation</h2>
                    <p>Êtes-vous sûr de vouloir valider ces entrées ?</p>
                    <p><strong>Utilisateur :</strong> <?= $row_data['week']->getUser()->getName(); ?></p>
                    <p><strong>Semaine :</strong> <?= $row_data['week']->getWeek(); ?></p>
                    <p><strong>Entrées :</strong></p>
                    <?php foreach ($row_data['week']->getData() as $week_data): ?>
                        <div class="entry-container">
                            <p><strong>Projet :</strong> <?= $week_data['project']->getName(); ?></p>
                            <?php if ($week_data['work']->getDescription()): ?>
                                <p><strong>Description :</strong> <?= $week_data['work']->getDescription(); ?></p>
                            <?php endif; ?>
                            <p><strong>Heures :</strong> <?= $week_data['work']->getCount(); ?> h</p>
                            <?php
                            $day = $week_data['work']->getDay();
                            if ($day !== null && $day != 0): ?>
                                <p><strong>Jour :</strong> <?= getFrenchDayName($day); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class='modal-footer'>
                        <button class='close-modal'>Annuler</button>

                        <!-- Validation -->
                        <form method="POST" action="/heures/valider" class="validate_form">
                            <?php foreach ($row_data['week']->getData() as $week_data): ?>
                                <input type="hidden" name="work_id[]" value="<?= $week_data['work']->getId(); ?>">
                            <?php endforeach; ?>
                            <input type="hidden" name="work_status" value="<?= WorkStatus::CONFIRME ?>">
                            <?php CsrfService::insertToken(); ?>
                            <button type="submit" class="button success">Valider les heures</button>
                        </form>

                        <!-- Refus -->
                        <form method="POST" action="/heures/valider" class="validate_form">
                            <?php foreach ($row_data['week']->getData() as $week_data): ?>
                                <input type="hidden" name="work_id[]" value="<?= $week_data['work']->getId(); ?>">
                            <?php endforeach; ?>
                            <input type="hidden" name="work_status" value="<?= WorkStatus::EN_COURS_DE_CREATION ?>">
                            <?php CsrfService::insertToken(); ?>
                            <button type="submit" class="button danger">Refuser les heures</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>