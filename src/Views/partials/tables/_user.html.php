<?php

use Src\Models\Enums\Status\WorkStatus;
use Src\Models\Enums\Status\UserStatus;
use Src\Services\AuthService;
use Src\Services\CsrfService;

?>
<div data-update="/update/utilisateurs" data-delete="/delete/utilisateurs" data-create="/utilisateurs">
    <form action="">
        <div class="top-bar">
            <div class="filter-container">
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
                        foreach ($data as $row_data):
                            $name = $row_data['user']->getName();
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
                    <label for="status">Statut :</label>
                    <select id="status" name="status" onchange="this.form.submit()">
                        <option value="" <?= ($status === null || $status === '') ? 'selected' : ''; ?>>-- Tous les status --</option>
                        <?php foreach (UserStatus::getEnumOptions() as $value => $label): ?>
                            <option value="<?= $value; ?>" <?= ($status !== null && (string)$status === (string)$value) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($label, ENT_QUOTES); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-container">
                    <label for="role_id">Rôle :</label>
                    <select id="role_id" name="role_id" onchange="this.form.submit()">
                        <option value="" <?= ($role_id === null || $role_id === '') ? 'selected' : ''; ?>>-- Tous les rôles --</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role->getId(); ?>" <?= ($role_id == $role->getId()) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($role->getName(), ENT_QUOTES); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="button primary">Filtrer</button>
                <a href="?">Réinitialiser</a>
            </div>

            <div class="container-btn-create">
                <a href="#" data-modal="create">Créer</a>
            </div>
        </div>
    </form>
    <table class="table-gen">
        <thead>
            <tr>
                <th>Nom complet</th>
                <th>Contact</th>
                <th>Role
                    <small style="color:rgb(206, 206, 206);">
                        <?php
                        $title = '';
                        foreach ($roles as $role) {
                            $title .= $role->getFr() . ' : ' . $role->getDescription() . '&#10;';
                        }
                        ?>
                        <i class='bx bx-info-circle' title="<?= $title; ?>"></i>
                    </small>
                </th>
                <th>Date de création</th>
                <th>État</th>
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
                <?php else:
                foreach ($data as $id => $row_data): ?>
                    <tr>
                        <td>
                            <div class="profil">
                                <img src="<?= $row_data['user']->getPicture(); ?>"
                                    alt="Photo de profil de <?= $row_data['user']->getName(); ?>"
                                    class="photo-profil">
                                <?= $row_data['user']->getName(); ?>
                            </div>
                        </td>
                        <td>
                            <a href="mailto:<?= $row_data['user']->getEmail(); ?>">
                                <?= $row_data['user']->getEmail(); ?>
                            </a>
                        </td>
                        <td>
                            <?= $row_data['role']->getFr(); ?>
                        </td>
                        <td><?= $row_data['user']->getCreation(); ?></td>
                        <td>
                            <div class="tag <?= UserStatus::getColor($row_data['user']->getStatus()) ?>">
                                <?= $row_data['user']->getStatus(fr: true); ?>
                            </div>
                        </td>
                        <td class="action">
                            <a href="#" class="button primary minimal" data-modal="read_<?= $id ?>"><i class='bx bx-show'></i>
                                <span>Détails</span></a>
                            <a href="#" class="button secondary minimal" data-modal="edit_<?= $id ?>"><i class='bx bx-edit'></i>
                                <span>Modifier</span></a>
                            <?php if ($row_data['user']->getId() != AuthService::getUser()->getId()): ?>
                                <a href="#" class="button danger minimal" data-modal="delete_<?= $id ?>"><i class='bx bx-trash'></i>
                                    <span>Supprimer</span></a>
                            <?php endif; ?>
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
            <p><strong>Nom :</strong> <?= $row_data['user']->getLastname(); ?></p>
            <p><strong>Prénom :</strong> <?= $row_data['user']->getFirstname(); ?></p>
            <p><strong>Email :</strong> <?= $row_data['user']->getEmail(); ?></p>
            <p><strong>Rôle :</strong> <?= $row_data['role']->getFr(); ?></p>
            <p><strong>Date de création :</strong> <?= $row_data['user']->getCreation(); ?></p>
            <p><strong>État :</strong> <?= $row_data['user']->getStatus(fr: true); ?></p>
            <?php require __DIR__ . '/../modals/read/_bottom.html.php'; ?>

            <?php require __DIR__ . '/../modals/edit/_top.html.php'; ?>
            <div class="input-container">
                <label for="user_firstname">Prénom</label>
                <input type="text" name="user_firstname" id="user_firstname"
                    value="<?= $row_data['user']->getFirstname(); ?>" required>
            </div>
            <div class="input-container">
                <label for="user_lastname">Nom</label>
                <input type="text" name="user_lastname" id="user_lastname" value="<?= $row_data['user']->getLastname(); ?>"
                    required>
            </div>
            <div class="input-container">
                <label for="user_email">Email</label>
                <input type="email" name="user_email" id="user_email" value="<?= $row_data['user']->getEmail(); ?>"
                    required>
            </div>
            <input type="hidden" name="user_id" value="<?= $row_data['user']->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
            <?php require __DIR__ . '/../modals/edit/_bottom.html.php'; ?>

            <?php require __DIR__ . '/../modals/delete/_top.html.php'; ?>
            <input type="hidden" name="user_id" value="<?= $row_data['user']->getId(); ?>">
            <input type="hidden" name="page" value="<?= $pages['current_page']; ?>">
        <?php require __DIR__ . '/../modals/delete/_bottom.html.php';
        endforeach; ?>
        <?php require __DIR__ . '/../modals/create/_top.html.php'; ?>
        <div class="input-container">
            <label for="user_firstname">Prénom</label>
            <input type="text" name="user_firstname" id="user_firstname" required>
        </div>
        <div class="input-container">
            <label for="user_lastname">Nom</label>
            <input type="text" name="user_lastname" id="user_lastname" required>
        </div>
        <div class="input-container">
            <label for="user_email">Email</label>
            <input type="email" name="user_email" id="user_email" required>
            <small>
                <i class='bx bx-info-circle'></i> Un email sera envoyé à l'utilisateur afin qu'il puisse définir son mot de passe.
            </small>
        </div>
        <div class="input-container">
            <label for="user_role">Rôle</label>
            <select name="user_role" id="user_role">
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role->getId(); ?>" <?= $role->getId() == 2 ? 'selected' : ''; ?>>
                        <?= $role->getFr(); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php require __DIR__ . '/../modals/create/_bottom.html.php'; ?>
    </div>
</div>