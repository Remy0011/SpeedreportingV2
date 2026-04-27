<?php

namespace Src\Controller;

use Src\Core\ErrorKernel;
use Src\Managers\ClientManager;
use Src\Models\Client;
use Src\Services\CsrfService;

class ClientController extends BaseController
{
    /**
     * Affiche la liste des clients.
     * Cette méthode gère les filtres de recherche et de type,
     * ainsi que la pagination des résultats.
     * Elle récupère les données des clients depuis le gestionnaire de clients
     * et les affiche dans la vue principale.
     * 
     * @return void
     */
    public function getIndex()
    {
        // Filtres
        $filters = [
            'search' => $_GET['search'] ?? null,
            'type'   => $_GET['type'] ?? null,
        ];

        // Pagination
        $pages = $this->paginate(
            manager: new ClientManager(),
            function: 'getTableCount',
            criteria: $filters
        );

        // Données
        $rows_raw = (new ClientManager())->getTableData(
            page: $pages['current_page'],
            limit: 10,
            filters: $filters
        );

        $clients = [];
        foreach ($rows_raw as $row) {
            $clients[] = new Client($row);
        }

        $projectsByClient = (new ClientManager())->getProjectsGroupedByClient();

        // Vue AJAX
        $this::renderAjax('partials/tables/_client', [
            'data' => $clients,
            'pages' => $pages,
            'search' => $filters['search'],
            'type' => $filters['type'],
            'projectsByClient' => $projectsByClient,
        ]);

        // Vue principale
        $this::render('Client/index', [
            'data' => $clients,
            'pages' => $pages,
            'search' => $filters['search'],
            'type' => $filters['type'],
            'projectsByClient' => $projectsByClient,
        ]);
    }


    /**
     * Supprime un client.
     * Cette méthode vérifie le token CSRF pour éviter les attaques CSRF,
     * et s'assure que la requête est une requête AJAX.
     * Elle récupère l'ID du client à supprimer depuis les données POST,
     * puis supprime le client correspondant
     * en utilisant le gestionnaire de clients.
     * Si le client n'est pas trouvé, une erreur 404 est renvoyée.
     * 
     * @return void
     */
    public function deleteClient()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $client_id = $_POST['client_id'] ?? null;
        if (!$client_id) {
            ErrorKernel::throwHttpError(400, "L'ID du client est requis.");
        }

        $client_raw = (new ClientManager())->find($client_id);
        if (!$client_raw) {
            ErrorKernel::throwHttpError(404, "Client non trouvé.");
        }

        $client = new Client($client_raw);
        (new ClientManager())->delete($client->getId());

        $this->getIndex();
    }

    /**
     * Met à jour un client.
     * Cette méthode vérifie le token CSRF pour éviter les attaques CSRF,
     * et s'assure que la requête est une requête AJAX.
     * Elle récupère l'ID du client à mettre à jour depuis les données POST,
     * puis met à jour le client correspondant
     * en utilisant le gestionnaire de clients.
     * Si le client n'est pas trouvé, une erreur 404 est renvoyée.
     * 
     * @return void
     */
    public function updateClient()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $client_id = $_POST['client_id'] ?? null;
        if (!$client_id) {
            ErrorKernel::throwHttpError(400, "L'ID du client est requis.");
        }

        $client_raw = (new ClientManager())->find($client_id);
        if (!$client_raw) {
            ErrorKernel::throwHttpError(404, "Client non trouvé.");
        }

        $client = new Client($client_raw);
        $client->hydrate($_POST);

        (new ClientManager())->save($client);

        $this->getIndex();
    }

    /**
     * Crée un nouveau client.
     * Cette méthode vérifie le token CSRF pour éviter les attaques CSRF,
     * et s'assure que la requête est une requête AJAX.
     * Elle récupère les données du client depuis les données POST,
     * puis crée un nouveau client en utilisant le gestionnaire de clients.
     * Si les données requises ne sont pas fournies, une erreur 400 est renvoyée.
     * 
     * @return void
     */
    public function postClient()
    {
        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        $this::verifyRequiredFields([
            'client_name'
        ]);

        $client = new Client($_POST);

        (new ClientManager())->save($client);

        $this->getIndex();
    }
}
