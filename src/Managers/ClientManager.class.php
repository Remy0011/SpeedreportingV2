<?php

namespace Src\Managers;

class ClientManager extends BaseManager
{
    protected static ?string $table = 'client';

    /**
     * Récupère les options de la table client sous forme de tableau associatif.
     * 
     * @return array Tableau associatif des options (id => nom).
     */
    public function getOptions(): array
    {
        $stmt = $this->pdo->prepare("SELECT client_id AS id, client_name AS name FROM table_client ORDER BY client_name ASC");
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $options = [];
        foreach ($rows as $row) {
            $options[htmlspecialchars($row['id'])] = htmlspecialchars($row['name']);
        }

        return $options;
    }

    /**
     * Retourne une liste paginée des clients pour affichage dans un tableau.
     * Cette méthode permet de récupérer les données des clients
     * en fonction de la page et de la limite spécifiées, avec des filtres optionnels.
     * 
     * @param int $page Numéro de la page à récupérer (1 par défaut).
     * @param int $limit Nombre de résultats par page (10 par défaut).
     * @param array|null $filters Filtres optionnels pour la recherche (par nom ou type de client).
     * 
     * @return array Tableau associatif contenant les données des clients.
     */
    public function getTableData(int $page = 1, int $limit = 10, ?array $filters = []): array
    {
        $offset = ($page - 1) * $limit;

        $query = "SELECT * FROM table_client WHERE client_id != 0";
        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (client_name LIKE :search OR client_type LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['type'])) {
            $query .= " AND client_type = :type";
            $params[':type'] = $filters['type'];
        }

        $query .= " ORDER BY client_name ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, \PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne le nombre total de clients pour la pagination.
     * Cette méthode permet de compter le nombre de clients
     * en appliquant les filtres de recherche et de type si spécifiés.
     * 
     * @return int Nombre total de clients.
     * 
     * @throws \PDOException Si une erreur de base de données se produit.
     */
    public function getTableCount(?array $filters = []): int
    {
        $query = "SELECT COUNT(client_id) AS count FROM table_client WHERE client_id != 0";
        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (client_name LIKE :search OR client_type LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['type'])) {
            $query .= " AND client_type = :type";
            $params[':type'] = $filters['type'];
        }

        $stmt = $this->pdo->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, \PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupère les stats de projet par type de client.
     * Cette méthode permet de récupérer le nombre de projets
     * regroupés par leur type de client.
     * 
     * @return array Un tableau associatif contenant les types de clients et le nombre de projets pour chaque type.
     */
    public function getClientProjectCounts(): array
    {
        $query = "SELECT c.client_type, COUNT(p.project_id) AS project_count
              FROM table_client c
              LEFT JOIN table_project p ON c.client_id = p.project_client AND p.project_type = 'travail'
              GROUP BY c.client_type;";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Supprime un client (et éventuellement gère les projets liés).
     * Cette méthode supprime un client de la base de données
     * en fonction de son ID. Si le client a des projets associés,
     * il peut être nécessaire de gérer ces projets (par exemple, les supprimer ou les réaffecter).
     * 
     * @param int $id L'ID du client à supprimer.
     * 
     * @return bool Retourne true si la suppression a réussi, false sinon.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM table_client WHERE client_id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Récupère les projets regroupés par client.
     * Cette méthode permet de récupérer tous les projets
     * et de les regrouper par client, en renvoyant un tableau associatif
     * où la clé est l'ID du client et la valeur est un tableau des projets associés.
     * 
     * @return array Un tableau associatif regroupant les projets par client.
     */
    public function getProjectsGroupedByClient(): array
    {
        $sql = "SELECT project_client, project_name FROM table_project ORDER BY project_client, project_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $grouped = [];

        foreach ($projects as $project) {
            $clientId = $project['project_client'];
            if (!isset($grouped[$clientId])) {
                $grouped[$clientId] = [];
            }
            $grouped[$clientId][] = [
                'project_name' => $project['project_name'],
            ];
        }

        return $grouped;
    }
}
