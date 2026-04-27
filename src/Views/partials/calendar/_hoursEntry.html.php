<?php

use Src\Services\CsrfService;

?>
<?php if ($hours_left > 0): ?>
    <form method="post" action="/mes-heures" id="hours-entry-form">
        <input type="hidden" name="command" value="create" />
        <div class="form-container">
            <div class="input-container">
                <label for="work_project">Nom du projet :</label>
                <select name="work_project" id="work_project" required>
                    <optgroup label="Absences">
                        <option value="1">Congés</option>
                        <option value="2">Maladie</option>
                        <option value="3">Absence</option>
                    </optgroup>
                    <optgroup label="Projets">
                        <?php foreach ($project_options as $value => $option): ?>
                            <option value="<?= $value; ?>">
                                <?= $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="input-container">
                <label for="work_description">Description du travail effectué : </label>
                <textarea name="work_description" id="work_description" placeholder="Décrivez ce que vous avez effectué (250 caractères maximum)" maxlength="250" style="height: 4.5rem;"></textarea>
            </div>
            <div class="input-container">
                <label for="work_count">Durée : </label>
                <input type="number" name="work_count" id="work_count" max="<?= $hours_left ?>" min="0.5" step="0.5" required />
                <div class="info-container">
                    <a class="button primary info-button">i</a>
                    <span class="info-label">
                        <p>Les minutes sont en décimals (Exemple: 0,5 = 30 minutes)</p>
                    </span>
                </div>
            </div>
            <div class="input-container">
                <label for="day_selector">Jour : </label>
                <select id="day_selector">
                    <option value="">Jour non précisé</option>
                </select>
            </div>

            <div class="input-container">
                <label for="work_date">Date : </label>
                <input type="date" name="work_date" id="work_date" readonly />
            </div>
            <div class="input-container validate">
                <button type="submit" class="button success">Ajouter</button>
            </div>
        </div>
        <?php CsrfService::insertToken(); ?>
    </form>
<?php elseif ($can_validate): ?>
    <div class="input-container validate-all">
        <form action="/valider/mes-heures" method="POST">
            <button type="submit" id="validate-all" class="button success">Valider toutes les heures</button>
            <?php CsrfService::insertToken(); ?>
        </form>
    </div>
<?php endif; ?>