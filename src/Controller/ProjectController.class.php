<?php

namespace Src\Controller;

use Src\Core\ErrorKernel;
use Src\Managers\ClientManager;
use Src\Managers\ProjectManager;
use Src\Models\Client;
use Src\Models\Project;
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
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'client_id' => $_GET['client_id'] ?? null,
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
        $rows_raw = (new ProjectManager())->getTableData(
            page: $pages['current_page'],
            limit: 10,
            filters: $filters
        );

        $data = [];
        foreach ($rows_raw as $row) {
            $project = new Project($row);
            $client = new Client($row);

            $progression = $projectManager->getProjectProgression($project->getId());

            $data[] = [
                'project' => $project,
                'client' => $client,
                'progression' => $progression,
            ];
        }

        // Clients disponibles pour les filtres
        $clients_raw = (new ClientManager())->fetchAll();
        $clients = [];
        foreach ($clients_raw as $client_row) {
            $client = new Client($client_row);
            $clients[$client->getId()] = $client;
        }

        // Vue Ajax (si utilisée en dynamique)
        $this::renderAjax('partials/tables/_project', [
            'data' => $data,
            'pages' => $pages,
            'clients' => $clients,
        ]);

        // Vue principale
        $this::render('Project/index', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'client_id' => $filters['client_id'],
            'start_year' => $filters['start_year'],
            'data' => $data,
            'pages' => $pages,
            'clients' => $clients,
        ]);
    }

    /**
     * Affiche le détail d'un projet.
     * Cette méthode récupère l'ID du projet depuis les paramètres de la requête
     * et affiche les détails du projet correspondant.
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
     * Gère la soumission du formulaire de création de projets.
     * 
     * Liste des champs attendu dans le formulaire :
     * 
     * - project_name : text - obligatoire - Nom du projet
     * - project_description : text - optionnel - Description du projet
     * - project_resource : number - optionnel - Ressources allouées au projet
     * - project_start : date - optionnel - Date de début du projet
     * - project_end : date - optionnel - Date de fin du projet
     * - project_status : string - optionnel - Etat du projet (ENUM)
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
