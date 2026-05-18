<?php

namespace Src\Controller;

use Src\Core\ErrorKernel;
use Src\Managers\ClientManager;
use Src\Managers\ProjectManager;
use Src\Managers\UserManager;
use Src\Models\Client;
use Src\Models\Project;
use Src\Models\User;
use Src\Services\CsrfService;

class ProjectController extends BaseController
{
    /**
     * Affiche la liste des projets.
     * Cette méthode gère les filtres de recherche, de statut et de client,
     * et la pagination des résultats.
     *
     * @return void
     */
    public function getIndex()
    {
        // Préparation des filtres
        $filters = [
            'search'     => $_GET['search'] ?? null,
            'status'     => $_GET['status'] ?? null,
            'client_id'  => $_GET['client_id'] ?? null,
            'start_year' => $_GET['start_year'] ?? null,
        ];

        // Pagination
        $pages = $this->paginate(
            manager: new ProjectManager(),
            function: 'getTableCount',
            criteria: $filters
        );

        // Données projets
        $projectManager = new ProjectManager();
        $rows_raw = $projectManager->getTableData(
            page: $pages['current_page'],
            limit: 10,
            filters: $filters
        );

        $data = [];
        foreach ($rows_raw as $row) {
            $project    = new Project($row);
            $client     = new Client($row);
            $progression = $projectManager->getProjectProgression($project->getId());

            // Liste des utilisateurs assignés au projet
            $users = $projectManager->getProjectUsers($project->getId());

            $data[] = [
                'project'     => $project,
                'client'      => $client,
                'progression' => $progression,
                'users'       => $users,  // array of ['user_id', 'user_firstname', 'user_lastname', 'user_email', 'user_picture']
            ];
        }

        // Clients disponibles pour les filtres
        $clients_raw = (new ClientManager())->fetchAll();
        $clients = [];
        foreach ($clients_raw as $client_row) {
            $client = new Client($client_row);
            $clients[$client->getId()] = $client;
        }

        // Tous les utilisateurs actifs — pour le picker d'assignation dans la modale edit
        $all_users_raw = (new UserManager())->fetchAll();
        $all_users = [];
        foreach ($all_users_raw as $user_row) {
            $user = new User($user_row);
            if ($user->getId() !== 0) {
                $all_users[$user->getId()] = $user;
            }
        }

        // Vue Ajax
        $this::renderAjax('partials/tables/_project', [
            'data'      => $data,
            'pages'     => $pages,
            'clients'   => $clients,
            'all_users' => $all_users,
        ]);

        // Vue principale
        $this::render('Project/index', [
            'search'     => $filters['search'],
            'status'     => $filters['status'],
            'client_id'  => $filters['client_id'],
            'start_year' => $filters['start_year'],
            'data'       => $data,
            'pages'      => $pages,
            'clients'    => $clients,
            'all_users'  => $all_users,
        ]);
    }

    /**
     * Supprime un projet.
     *
     * @return void
     */
    public function deleteProject()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $project_id = $_POST['project_id'] ?? null;
        if (!$project_id) {
            ErrorKernel::throwHttpError(400, "L'ID du projet est requis.");
        }

        $project_raw = (new ProjectManager())->find($project_id);
        if (!$project_raw) {
            ErrorKernel::throwHttpError(404, "Projet non trouvé.");
        }

        $project = new Project($project_raw);

        (new ProjectManager())->delete($project->getId());

        $this->getIndex();
    }

    /**
     * Met à jour un projet.
     * Cette méthode vérifie le token CSRF pour éviter les attaques CSRF,
     * et s'assure que la requête est une requête AJAX.
     * Elle récupère l'ID du projet à mettre à jour depuis les données POST,
     * hydrate l'objet Projet avec les nouvelles données,
     * et enregistre les modifications dans la base de données.
     *
     * @return void
     */
    public function updateProject()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $project_id = $_POST['project_id'] ?? null;
        if (!$project_id) {
            ErrorKernel::throwHttpError(400, "L'ID du projet est requis.");
        }

        $project_raw = (new ProjectManager())->find($project_id);
        if (!$project_raw) {
            ErrorKernel::throwHttpError(404, "Projet non trouvé.");
        }

        $project = new Project($project_raw);

        $project->hydrate($_POST);

        (new ProjectManager())->save($project);

        $this->getIndex();
    }

    /**
     * Assigne un utilisateur à un projet (appel AJAX).
     *
     * @return void
     */
    public function assignUser()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $project_id = $_POST['project_id'] ?? null;
        $user_id    = $_POST['user_id'] ?? null;

        if (!$project_id || !$user_id) {
            ErrorKernel::throwHttpError(400, "project_id et user_id sont requis.");
        }

        (new ProjectManager())->assignUser((int)$project_id, (int)$user_id);

        $this->getIndex();
    }

    /**
     * Retire un utilisateur d'un projet (appel AJAX).
     *
     * @return void
     */
    public function unassignUser()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $project_id = $_POST['project_id'] ?? null;
        $user_id    = $_POST['user_id'] ?? null;

        if (!$project_id || !$user_id) {
            ErrorKernel::throwHttpError(400, "project_id et user_id sont requis.");
        }

        (new ProjectManager())->unassignUser((int)$project_id, (int)$user_id);

        $this->getIndex();
    }

    /**
     * Gère la création d'un projet.
     *
     * @return void
     */
    public function postProject()
    {
        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        $this::verifyRequiredFields([
            'project_name',
        ]);

        // Créer un nouveau projet
        $project = new Project($_POST);
        $project->setClient(
            (isset($_POST['project_client']) && $_POST['project_client'] !== 'null')
                ? $_POST['project_client']
                : null
        );

        (new ProjectManager())->save($project);

        $this->getIndex();
    }
}
