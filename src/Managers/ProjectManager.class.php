<?php

namespace Src\Managers;

/**
 * Clasee pour gérer les rôles dans la base de données.
 * Elle hérite de la classe de base BaseManager.
 */
class ProjectManager extends BaseManager
{
    protected static ?string $table = 'project';

    /**
     * Récupère les options de la table projet sous forme de tableau associatif.
     * 
     * @return array Tableau associatif des options (id => nom).
     */
    public function getOptions()
    {
        // TODO : Liste de projets disponibles pour l'utilisateur connecté

        // Prépare et exécute la requête pour récupérer les données
        $stmt = $this->pdo->prepare(
            "SELECT project_id AS id, project_name AS name 
                    FROM table_project 
                    WHERE project_type = 'travail' 
                    ORDER BY project_name ASC"
        );
        $stmt->execute();

        // Récupère les résultats et les transforme en tableau associatif
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $options = [];
        foreach ($rows as $row) {
            $options[htmlspecialchars($row['id'])] = htmlspecialchars($row['name']);
        }

        return $options;
    }

    /**
     * Récupère les projets de l'utilisateur avec leur progression.
     * Cette méthode récupère les projets associés à un utilisateur
     * et calcule la progression de chaque projet en pourcentage.
     * @param int $userId L'ID de l'utilisateur pour lequel récupérer les projets.
     * 
     * @return array Un tableau associatif contenant les projets et leur progression.
     */
    public function getUserProjectsWithProgression(int $userId): array
    {
        $query = "SELECT p.project_id, p.project_name, p.project_start, p.project_end, p.project_realend
        FROM table_project p
        JOIN table_project_user pu ON pu.project_user_project_id = p.project_id
        WHERE pu.project_user_user_id = :userId
          AND p.project_type = 'travail'
        ORDER BY p.project_start ASC
        LIMIT 3;";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];

        foreach ($projects as $project) {
            $start = strtotime($project['project_start']);
            $end = strtotime($project['project_end']);
            $now = time();

            $progress = 0;
            if ($now >= $start && $end > $start) {
                $progress = min(100, round((($now - $start) / ($end - $start)) * 100));
            } elseif ($now > $end) {
                $progress = 100;
            }

            $result[] = [
                'name' => $project['project_name'],
                'progress' => $progress
            ];
        }

        return $result;
    }

    /**
     * Fonction qui permet de récupèrer la progression d'un projet :
     * La barre est verte temps qu'on est pas à la fin du projet.
     * SI on arrive à la fin du projet, et que le nombre d'heure pour un projet : project_ressource, n'a pas été pointé suffisamment de fois (nombre d'heure pour les utilisateurs) alors on est en retards, la barre deviens orange/rouge
     * SI on est à la fin et qu'il nous manque du temps, alors on rajoute un surplus de temps (realend) qui s'affiche ne rouge (uniquement ce pourcentage en plus)
     * 
     * @param int $projectId L'ID du projet pour lequel récupérer la progression.
     * 
     * @return array Un tableau associatif contenant les informations du projet et sa progression.
     * 
     * @throws \Exception Si le projet n'est pas trouvé.
     */
    public function getProjectProgression(int $projectId): array
    {
        $query = "SELECT project_id, project_name, project_start, project_end, project_realend, project_resource,
                SUM(work_count) AS total_worked_hours
            FROM table_project
            LEFT JOIN table_work ON table_work.work_project = table_project.project_id
            WHERE project_id = :projectId
            GROUP BY project_id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':projectId', $projectId, \PDO::PARAM_INT);
        $stmt->execute();

        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$project) {
            throw new \Exception("Projet non trouvé.");
        }

        $resourceHours = (float)$project['project_resource'];
        $workedHours = (float)$project['total_worked_hours'] ?? 0;

        // Calcul simple du pourcentage de progression
        $progression = 0;
        if ($resourceHours > 0) {
            $progression = min(100, round(($workedHours / $resourceHours) * 100));
        }

        return [
            'name' => $project['project_name'],
            'progression' => $progression,
            'start' => $project['project_start'],
            'end' => $project['project_end'],
            'realEnd' => $project['project_realend'],
            'workedHours' => $workedHours,
            'resourceHours' => $resourceHours
        ];
    }

    /**
     * Récupère les données pour la table des projets avec pagination et filtres.
     * Cette méthode récupère les projets de la base de données
     * en appliquant des filtres de recherche, de client, de statut et d'année de début.
     * 
     * @param int $page Le numéro de la page à récupérer (par défaut 1).
     * @param int $limit Le nombre d'entrées par page (par défaut 10).
     * @param array $filters Les filtres à appliquer (recherche, client_id, statut, start_year).
     * 
     * @return array Un tableau associatif contenant les données des projets.
     * @throws \Exception Si une erreur de base de données se produit.
     */
    public function getTableData(int $page = 1, int $limit = 10, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $query = "SELECT project_id, project_name, project_description, project_resource, project_dev,
                project_creation, project_start, project_end, project_realend, project_status,
                client_id, client_name, client_type
            FROM table_project 
            LEFT JOIN table_client ON table_project.project_client = table_client.client_id
            WHERE project_id != 0";

        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND project_name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['client_id'])) {
            $query .= " AND project_client = :client_id";
            $params[':client_id'] = $filters['client_id'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND project_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['start_year'])) {
            $query .= " AND YEAR(project_start) = :start_year";
            $params[':start_year'] = $filters['start_year'];
        }

        $query .= " LIMIT $limit OFFSET $offset";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des données : " . $e->getMessage());
        }
    }

    /** 
     * Récupère le nombre total de projets pour la pagination.
     * Cette méthode compte le nombre de projets dans la base de données
     * en appliquant des filtres de recherche, de client, de statut et d'année de début.
     * 
     * @param array|null $filters Les filtres à appliquer (recherche, client_id, statut, start_year).
     * 
     * @return int Le nombre total de projets.
     * 
     * @throws \Exception Si une erreur de base de données se produit.
     */
    public function getTableCount(?array $filters = null): int
    {
        $query = "SELECT COUNT(project_id) AS count FROM table_project WHERE project_id != 0";
        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND project_name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['client_id'])) {
            $query .= " AND project_client = :client_id";
            $params[':client_id'] = $filters['client_id'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND project_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['start_year'])) {
            $query .= " AND YEAR(project_start) = :start_year";
            $params[':start_year'] = $filters['start_year'];
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors du comptage des projets : " . $e->getMessage());
        }
    }

    /**
     * Supprime un projet de la base de données.
     * Cette méthode supprime un projet en fonction de son ID.
     * Avant de supprimer le projet, elle met à jour les entrées de la table work
     * pour réaffecter le projet à 0 (aucun projet).
     * @param int $id L'ID du projet à supprimer.
     * 
     * @return bool Retourne true si la suppression a réussi, false sinon.
     * @throws \Exception Si une erreur de base de données se produit.
     */
    public function delete(int $id): bool
    {
        try {
            $updateQuery = "UPDATE table_work SET work_project = 0 WHERE work_project = :id";
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->execute([':id' => $id]);

            $deleteQuery = "DELETE FROM table_project WHERE project_id = :id";
            $deleteStmt = $this->pdo->prepare($deleteQuery);
            return $deleteStmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la suppression du projet : " . $e->getMessage());
        }
    }
}
