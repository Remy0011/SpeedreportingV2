<?php

use Src\Managers\UserManager;
use Src\Models\User;

$admin_row = (new UserManager())->getAdminUser();
$admin = $admin_row ? new User($admin_row) : null;
?>

<section class="section-help">
    <div class="help-container">
        <div class="nav-help">
            <div class="nav-content">
                <ul>
                    <li data-help="notice">Notice</li>
                    <li data-help="FAQ">FAQ</li>
                    <li data-help="contact">Contact</li>
                </ul>
            </div>
        </div>

        <div class="help-content">
            <div class="notice-help" data-content="notice">
                <h2>Notice</h2>

                <div class="notice-menu">
                    <button class="notice-tab" data-tab="intro">Introduction</button>
                    <button class="notice-tab" data-tab="usage">Utilisation</button>
                    <button class="notice-tab" data-tab="regles">Règles</button>
                </div>

                <div class="notice-tab-content" data-tab-content="intro">
                    <p>Contenu pour Introduction</p>
                </div>
                <div class="notice-tab-content" data-tab-content="usage">
                    <p>Contenu pour Utilisation</p>
                </div>
                <div class="notice-tab-content" data-tab-content="regles">
                    <p>Contenu pour Règles</p>
                </div>
            </div>

            <div class="FAQ-help" data-content="FAQ">
                <h2>Foire aux questions</h2>

                <ul>
                    <li>
                        <h3>Comment saisir mes heures de travail ?</h3>
                        <p class="answer">
                            Pour saisir vos heures de travail, connectez-vous à votre compte, allez dans la section
                            "Horaires".
                            <br />
                            Un formulaire vous permettra de saisir vos heures. Indiquez le projet concerné, ainsi que la
                            quantité d'heures travaillées.
                            <br />
                            Vous pouvez ajouter des commentaires pour préciser la nature de votre travail.
                            Il est également possible de saisir des heures pour un jour particulier de la semaine.
                            <br />
                            Une fois le formulaire rempli, cliquez sur "Ajouter" pour sauvegarder vos heures.
                            <br />
                            Saisissez toutes les heures de votre semaine de travail, puis appuyez sur le bouton 'Valider
                            toutes les heures' pour enregistrer vos saisies.
                        </p>
                    </li>

                    <li>
                        <h3>Que faire si je n'ai pas travaillé de la semaine ?</h3>
                        <p class="answer">
                            Si vous n'avez pas travaillé durant la semaine, il est possible de choisir le projet
                            'Congés', 'Maladies' ou 'Autres absences' dans le formulaire de saisie des heures, dans la rubriques
                            'Absences'.
                        </p>
                    </li>
                </ul>
            </div>

            <div class="contact-help" data-content="contact">
                <h2>Contact</h2>
                <div>
                    <h3>Vous avez besoin d'aide ?</h3>
                    <p>Contactez l'administrateur à l'adresse suivante :</p>
                    <?php if ($admin): ?>
                        <div class="form-content">
                            <label for="contact-prénom">Nom complet</label>
                            <input type="text" id="contact-prénom" name="contact-prénom"
                                value="<?= $admin->getName(); ?>">
                        </div>
                        <div class="form-content">
                            <label for="contact-email">Mail</label>
                            <input type="email" id="contact-email" name="contact-email"
                                value="<?= $admin->getEmail(); ?>">
                        </div>
                    <?php else: ?>
                        <p>Aucun admin trouvé</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>