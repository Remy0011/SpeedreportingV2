<div>
    <table id="calendar-table" data-loader="local">
        <thead>
            <tr>
                <?php foreach ($weeks as $number => $label): ?>
                    <th scope="col">N° <?= $number ?><br /><?= $label; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php foreach ($weeks_data as $week_entries): ?>
                    <td>
                        <?php foreach ($week_entries as $data): ?>
                            <div class="hour-card <?= $data['work']->getStatus(); ?>">
                                <p><?= $data['project']->getName(); ?> - <span><?= $data['work']->getCount(); ?>h</span></p>
                                <div class="hour-card-actions">
                                    <a href="#" class="button primary" data-modal="read_<?= $data['work']->getId(); ?>">
                                        <i class='bx bx-show'></i>
                                    </a>
                                    <?php if ($data['work']->getStatus() === 'en_cours_de_creation'): ?>
                                        <a href="#" class="button contrast" data-modal="edit_<?= $data['work']->getId(); ?>">
                                            <i class='bx bx-edit'></i>
                                        </a>
                                        <a href="#" class="button danger" data-modal="delete_<?= $data['work']->getId(); ?>">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <?php foreach ($weeks as $number => $label): ?>
                    <td>
                        <p class="total-hours">
                            <?php
                            $total_hours = 0;
                            foreach ($weeks_data[$number] as $data) {
                                $total_hours += $data['work']->getCount();
                            }
                            echo "$total_hours h";
                            ?>
                        </p>
                    </td>
                <?php endforeach; ?>
            </tr>
        </tfoot>
    </table>

    <div class="week-blocks">
        <?php foreach ($weeks as $index => $label): ?>
            <div class="week-column">
                <div class="week-header">
                    <strong>N° <?= $index ?></strong><br>
                    <?= $label ?>
                </div>
                <div class="week-body">
                    <?php foreach ($weeks_data[$index] as $data): ?>
                        <div class="hour-card <?= $data['work']->getStatus(); ?>">
                            <p><?= $data['project']->getName(); ?> - <span><?= $data['work']->getCount(); ?>h</span></p>
                            <div class="hour-card-actions">
                                <a href="#" class="button primary" data-modal="read_<?= $data['work']->getId(); ?>">
                                    <i class='bx bx-show'></i>
                                </a>
                                <?php if ($data['work']->getStatus() === 'en_cours_de_creation'): ?>
                                    <a href="#" class="button contrast" data-modal="edit_<?= $data['work']->getId(); ?>">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="#" class="button danger" data-modal="delete_<?= $data['work']->getId(); ?>">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="week-footer">
                    <strong>Total : <?php
                            $total_hours = 0;
                            foreach ($weeks_data[$number] as $data) {
                                $total_hours += $data['work']->getCount();
                            }
                            echo "$total_hours h";
                            ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="modals">
        <?php foreach ($weeks_data as $week => $work_data):
            foreach ($work_data as $id => $raw_data): ?>
                <?php require __DIR__ . '/../modals/read/_top.html.php'; ?>
                <p><strong>Projet :</strong> <?= $raw_data['project']->getName(); ?></p>
                <p><strong>Heures :</strong> <?= $raw_data['work']->getCount(); ?>h</p>
                <p><strong>Date :</strong> <?= $raw_data['work']->getDate(); ?></p>
                <p><strong>Semaine:</strong> <?= $raw_data['work']->getWeek(); ?></p>
                <p><strong>État:</strong> <?= $raw_data['work']->getStatus(fr: true); ?></p>
                <p><strong>Description:</strong>
                    <?= !empty($raw_data['work']->getDescription()) ? $raw_data['work']->getDescription() : 'Non renseignée'; ?>
                </p>
                <p><strong>Créé le:</strong> <?= $raw_data['work']->getCreation(); ?></p>
                <?php require __DIR__ . '/../modals/read/_bottom.html.php'; ?>
                <?php if ($raw_data['work']->getStatus() === 'en_cours_de_creation'): ?>
                    <?php require __DIR__ . '/../modals/edit/_top.html.php'; ?>
                    <div class="input-container">
                        <label for="work_count">Nombre d'heures:</label>
                        <input type="number" id="work_count" name="work_count" min="0.5" max="<?= $raw_data['work']->getCount(raw:true) + $hours_left; ?>" step="0.5"
                            value="<?= $raw_data['work']->getCount(); ?>" required>
                    </div>
                    <div class="input-container">
                        <label for="work_description">Description:</label>
                        <textarea id="work_description" name="work_description" rows="4" maxlength="250"
                            placeholder="Décrivez ce que vous avez effectué (250 caractères max)"
                            style="resize: none;"><?= $raw_data['work']->getDescription(); ?></textarea>
                    </div>
                    <input type="hidden" name="work_id" value="<?= $raw_data['work']->getId(); ?>">
                    <input type="hidden" name="current_month" value="<?= $months['current']['month']; ?>">
                    <input type="hidden" name="current_year" value="<?= $months['current']['year']; ?>">
                    <?php require __DIR__ . '/../modals/edit/_bottom.html.php'; ?>

                    <?php require __DIR__ . '/../modals/delete/_top.html.php'; ?>
                    <input type="hidden" name="current_month" value="<?= $months['current']['month']; ?>">
                    <input type="hidden" name="current_year" value="<?= $months['current']['year']; ?>">
                    <input type="hidden" name="work_id" value="<?= $raw_data['work']->getId(); ?>">
                    <?php require __DIR__ . '/../modals/delete/_bottom.html.php'; ?>
                <?php endif; ?>
        <?php
            endforeach;
        endforeach; ?>
    </div>
</div>