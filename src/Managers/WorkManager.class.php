<?php

namespace Src\Managers;

use Src\Models\Enums\Status\WorkStatus;

/**
 * Classe pour gérer les work dans la base de données.
 * Elle hérite de la classe de base BaseManager.
 */
class WorkManager extends BaseManager
{
    protected static ?string $table = 'work';

    /**
     * Retourne une liste paginée des travaux pour affichage dans un tableau.
     * Cette méthode permet de récupérer les données des travaux
     * en fonction de la page et de la limite spécifiées, avec des filtres optionnels.
     * 
     * @param int $page Numéro de la page à récupérer (1 par défaut).
     * @param int $limit Nombre de résultats par page (10 par défaut).
     * @param array|null $filters Filtres optionnels pour la recherche (par email, nom, statut, rôle).
     * @throws \Exception
     * 
     * @return array Tableau associatif contenant les données des travaux.
     */
    public function getTableData(int $page = 1, int $limit = 10, ?array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $limit = (int) $limit;
        $offset = (int) $offset;

        $query = "SELECT work_id, work_count, work_week, work_year, work_day, work_description, work_status, work_creation, 
                user_id, user_firstname, user_lastname, user_email, user_picture,
                project_id, project_name, project_description, project_creation, project_end, client_name, client_type 
            FROM table_work 
                LEFT JOIN table_project ON table_work.work_project = table_project.project_id 
                LEFT JOIN table_user ON table_work.work_user = table_user.user_id
                LEFT JOIN table_client ON table_project.project_client = table_client.client_id
            WHERE 1=1";

        $params = [];

        // Recherche globale
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $query .= " AND (
                user_firstname LIKE ? 
                OR user_lastname LIKE ? 
                OR project_name LIKE ? 
                OR client_name LIKE ?
                OR CONCAT(user_firstname, ' ', user_lastname) LIKE ?
                OR CONCAT(user_lastname, ' ', user_firstname) LIKE ?
            )";
            $params = array_merge($params, [$search, $search, $search, $search, $search, $search]);
        }

        // Filtre par statut
        if (!empty($filters['status'])) {
            $query .= " AND work_status = ?";
            $params[] = $filters['status'];
        }

        // Filtre par utilisateur
        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }

        // Filtre par projet
        if (!empty($filters['project_id'])) {
            $query .= " AND project_id = ?";
            $params[] = $filters['project_id'];
        }

        // Filtre par client
        if (!empty($filters['client_name'])) {
            $query .= " AND client_name = ?";
            $params[] = $filters['client_name'];
        }

        // Filtre par semaine / année
        if (!empty($filters['week'])) {
            $query .= " AND work_week = ?";
            $params[] = $filters['week'];
        }

        if (!empty($filters['year'])) {
            $query .= " AND work_year = ?";
            $params[] = $filters['year'];
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
     * Récupère les travaux en cours de création pour un utilisateur donné.
     * 
     * @param int $userId
     * @throws \Exception
     * 
     * @return array
     */
    public function getInCreationWork(int $userId)
    {
        return $this->getWeekUserWork(userId: $userId, status: ['in_creation']);
    }

    /**
     * Récupère les heures de travail d'un utilisateur pour un mois et une année donnés.
     * 
     * @param int $userId
     * @param string $month
     * @param string $year
     * @throws \Exception
     * @return array
     */
    public function getMonthUserWork(int $userId, string $month, string $year)
    {
        // Calculate all week numbers for the given month and year, considering year overlap
        $startDate = new \DateTimeImmutable("$year-$month-01");
        $endDate = $startDate->modify('last day of this month');
        $weeks = [];
        $weekYears = [];

        for ($date = $startDate; $date <= $endDate; $date = $date->modify('+1 day')) {
            $week = (int) $date->format('W');
            $weekYear = (int) $date->format('o'); // 'o' is ISO-8601 week-numbering year
            $key = $weekYear . '-' . $week;
            if (!isset($weeks[$key])) {
                $weeks[$key] = ['week' => $week, 'year' => $weekYear];
            }
        }

        if (empty($weeks)) {
            return [];
        }

        // Build WHERE clause for (work_week, work_year) pairs
        $wherePairs = [];
        $params = [$userId];
        foreach ($weeks as $pair) {
            $wherePairs[] = '(work_week = ? AND work_year = ?)';
            $params[] = $pair['week'];
            $params[] = $pair['year'];
        }
        $whereClause = implode(' OR ', $wherePairs);

        $query = "SELECT work_id, work_status, work_project, project_name, project_id, work_description, work_count, work_day, work_week, work_year, work_creation
            FROM table_work
            LEFT JOIN table_project ON work_project = project_id
            WHERE work_user = ? AND ($whereClause)";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des données : " . $e->getMessage());
        }
    }

    /**
     * Récupère les travaux d'un utilisateur pour une semaine et une année donnés.
     * @param int $userId
     * @param int|null $week
     * @param int|null $year
     * @param array|null $status
     * @throws \Exception
     * 
     * @return array
     */
    public function getWeekUserWork(int $userId, ?int $week = null, ?int $year = null, ?array $status = null)
    {
        $query = "SELECT work_id, work_status, work_project, project_name, project_id, work_description, work_count, work_day, work_week, work_year, work_creation
        FROM table_work
        LEFT JOIN table_project ON work_project = project_id
        WHERE work_user = ?";

        $params = [$userId];

        if ($status !== null) {
            $placeholders = implode(',', array_fill(0, count($status), '?'));
            $query .= " AND work_status IN ($placeholders)";
            $params = array_merge($params, $status);
        }

        if ($week !== null) {
            $query .= " AND work_week = ?";
            $params[] = $week;
        }

        if ($year !== null) {
            $query .= " AND work_year = ?";
            $params[] = $year;
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des données : " . $e->getMessage());
        }
    }

    /**
     * Supprime un travail par son ID.
     * Cette méthode supprime un enregistrement de travail
     * de la base de données en fonction de son ID.
     * 
     * @param int $id L'ID du travail à supprimer.
     * @throws \Exception
     * @return bool
     */
    public function delete(int $id): bool
    {
        $query = "DELETE FROM table_work WHERE work_id = ?";
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la suppression des données : " . $e->getMessage());
        }
    }

    /**
     * Récupère les travaux à valider (work_status = 'en_attente'), paginés.
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @throws \Exception
     * @return array
     */
    public function getToValidate(int $page = 1, int $limit = 10, ?array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT work_id, work_count, work_week, work_year, work_day, work_description, work_status, work_creation,
                user_id, user_firstname, user_lastname,
                project_id, project_name, project_description, project_creation, project_end
            FROM table_work
                LEFT JOIN table_project ON table_work.work_project = project_id
                LEFT JOIN table_user ON table_work.work_user = user_id
                LEFT JOIN table_client ON table_project.project_client = table_client.client_id
            WHERE work_status = 'en_attente'";

        $params = [];

        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $query .= " AND (
                user_firstname LIKE ? 
                OR user_lastname LIKE ? 
                OR project_name LIKE ?
                OR CONCAT(user_firstname, ' ', user_lastname) LIKE ?
                OR CONCAT(user_lastname, ' ', user_firstname) LIKE ?
            )";
            $params = array_merge($params, [$search, $search, $search, $search, $search]);
        }

        if (!empty($filters['project_id'])) {
            $query .= " AND project_id = ?";
            $params[] = $filters['project_id'];
        }

        if (!empty($filters['year'])) {
            $query .= " AND work_year = ?";
            $params[] = $filters['year'];
        }

        if (!empty($filters['week'])) {
            $query .= " AND work_week = ?";
            $params[] = $filters['week'];
        }

        $query .= " ORDER BY user_id ASC, work_year ASC, work_week ASC, work_day ASC
                LIMIT $limit OFFSET $offset";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si les IDs de travail existent dans la base de données.
     * 
     * @param array $work_ids Tableau d'IDs de travail à vérifier.
     * @throws \InvalidArgumentException Si les IDs ne sont pas un tableau.
     * @throws \Exception En cas d'erreur de base de données.
     * 
     * @return bool
     */
    public function verifyIds($work_ids)
    {
        if (!is_array($work_ids)) {
            throw new \InvalidArgumentException("Les IDs de travail doivent être un tableau.");
        }

        $placeholders = implode(',', array_fill(0, count($work_ids), '?'));
        $query = "SELECT COUNT(*) FROM table_work WHERE work_id IN ($placeholders)";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($work_ids);
            return (int) $stmt->fetchColumn() === count($work_ids);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification des IDs de travail : " . $e->getMessage());
        }
    }

    /**
     * Change le statut de plusieurs travaux.
     * Cette méthode met à jour le statut des travaux
     * dans la base de données en fonction des IDs fournis.
     * @param array $work_ids Tableau d'IDs de travail à mettre à jour.
     * @param string $status Le nouveau statut à appliquer.
     * @throws \InvalidArgumentException Si le statut est invalide ou si les IDs ne sont pas valides.
     * @throws \Exception En cas d'erreur de base de données.
     * 
     * @return bool
     */
    public function changeWorkStatus(array $work_ids, string $status): bool
    {
        if (!in_array($status, WorkStatus::getValues())) {
            throw new \InvalidArgumentException("Statut de travail invalide.");
        }

        if (!$this->verifyIds($work_ids)) {
            throw new \InvalidArgumentException("Un ou plusieurs IDs de travail sont invalides.");
        }

        $placeholders = implode(',', array_fill(0, count($work_ids), '?'));
        $query = "UPDATE table_work SET work_status = ? WHERE work_id IN ($placeholders)";

        try {
            $stmt = $this->pdo->prepare($query);
            $params = array_merge([$status], $work_ids);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour du statut des travaux : " . $e->getMessage());
        }
    }

    /**
     * Met à jour le statut des travaux d'un utilisateur en 'en_attente'.
     * Cette méthode met à jour le statut des travaux
     * de l'utilisateur spécifié dans la base de données.
     * @param int $userId L'ID de l'utilisateur dont les travaux doivent être mis à jour.
     * @throws \Exception En cas d'erreur de base de données.
     * 
     * @return bool
     */
    public function setUserWorkWaiting(int $userId): bool
    {
        $query = "UPDATE table_work SET work_status = 'en_attente' 
              WHERE work_user = ? AND work_status = 'en_cours_de_creation'";

        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$userId]);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour du statut des travaux : " . $e->getMessage());
        }
    }

    /**
     * Valide les travaux de l'utilisateur en cours de création.
     * Cette méthode met à jour le statut des travaux
     * de l'utilisateur spécifié dans la base de données.
     * @param int $user_id L'ID de l'utilisateur dont les travaux doivent être validés.
     * @throws \Exception Si l'utilisateur ne peut pas valider ses travaux.
     * 
     * @return bool
     */
    public function validateSelfUser(int $user_id)
    {
        if (!$this->canUserValidate($user_id)) {
            throw new \Exception("L'utilisateur ne peut pas valider ses travaux, il n'a pas 35 heures en cours de création.");
        }
        $query = "UPDATE table_work SET work_status = 'en_attente' WHERE work_user = ? AND work_status = 'en_cours_de_creation'";
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$user_id]);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour du statut des travaux : " . $e->getMessage());
        }
    }

    /** 
     * Vérifie si l'utilisateur peut valider ses travaux.
     * Un utilisateur peut valider ses travaux s'il a au moins 35 heures en cours de création.
     * 
     * @param int $userId L'ID de l'utilisateur à vérifier.
     * 
     * @return bool
     */
    public function canUserValidate(int $userId): bool
    {
        $total = $this->getUserHoursToValidateCount($userId);
        return ((float)$total) >= 35.0;
    }

    /**
     * Récupère le total des heures en cours de création pour un utilisateur.
     * Cette méthode calcule la somme des heures de travail
     * pour un utilisateur donné dont le statut est 'en_cours_de_creation'.
     *
     * @param int $user_id L'ID de l'utilisateur dont les heures doivent être récupérées.
     * @throws \Exception En cas d'erreur de base de données.
     * 
     * @return float Le total des heures en cours de création pour l'utilisateur.
     */
    public function getUserHoursToValidateCount($user_id)
    {
        $current_year = date('Y');
        $current_week = date('W');
        $query = "SELECT SUM(work_count) FROM table_work WHERE work_user = ? AND work_week = ? AND work_year = ?";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$user_id, $current_week, $current_year]);
            return $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification du total des heures en cours de création : " . $e->getMessage());
        }
    }

    public function canValidateUser($user_id): bool
    {
        $current_year = date('Y');
        $current_week = date('W');
        $query = "SELECT SUM(work_count) FROM table_work WHERE work_user = ? AND work_week = ? AND work_year = ? AND work_status = 'en_cours_de_creation'";
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$user_id, $current_week, $current_year]);
            $total = (float) $stmt->fetchColumn();
            return $total == 35;
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la vérification des travaux en cours de création : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de travaux à valider.
     * Cette méthode compte le nombre de travaux
     * dont le statut est 'en_attente', en tenant compte des filtres de recherche
     * et des utilisateurs.
     * @param array|null $params Paramètres de recherche optionnels (recherche par nom, prénom, projet, client).
     * @throws \Exception En cas d'erreur de base de données.
     * 
     * @return int Le nombre de travaux à valider.
     */
    public function countToValidate(?array $params = null): int
    {
        $query = "SELECT COUNT(*) 
        FROM (
            SELECT work_user, work_week, work_year
            FROM table_work
            WHERE work_status = 'en_attente'
            GROUP BY work_user, work_week, work_year
        ) AS unique_entries
        LEFT JOIN table_user ON unique_entries.work_user = table_user.user_id
        LEFT JOIN table_work ON unique_entries.work_user = table_work.work_user 
            AND unique_entries.work_week = table_work.work_week 
            AND unique_entries.work_year = table_work.work_year
        LEFT JOIN table_project ON table_work.work_project = table_project.project_id
        LEFT JOIN table_client ON table_project.project_client = table_client.client_id
        ";

        if (isset($params) && !empty($params) && isset($params['search']) && !empty($params['search'])) {
            $search = $params['search'];
            $search = "%{$search}%";
            $query .= " WHERE (user_firstname LIKE ? OR user_lastname LIKE ? OR project_name LIKE ? OR client_name LIKE ?)";
            $params = [$search, $search, $search, $search];
        } else {
            $params = [];
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(
                $params
            );
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors du comptage des travaux à valider : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre total de données dans la table des travaux.
     * Cette méthode compte le nombre total de lignes dans la table des travaux
     * en tenant compte des filtres de recherche et des utilisateurs.
     * @param array|null $criteria Critères de recherche optionnels (recherche par nom, prénom, projet, client, statut, utilisateur, semaine, année).
     * @throws \Exception En cas d'erreur de base de données.
     * 
     * @return int Le nombre total de lignes dans la table des travaux.
     */
    public function countTableData(?array $criteria = null)
    {
        $query = "SELECT COUNT(*) 
              FROM table_work 
              LEFT JOIN table_project ON table_work.work_project = table_project.project_id 
              LEFT JOIN table_user ON table_work.work_user = table_user.user_id
              LEFT JOIN table_client ON table_project.project_client = table_client.client_id";

        $where = [];
        $params = [];

        if (!empty($criteria)) {
            if (!empty($criteria['search'])) {
                $search = '%' . $criteria['search'] . '%';
                $where[] = "(user_firstname LIKE ? OR user_lastname LIKE ? OR project_name LIKE ? OR client_name LIKE ? OR CONCAT(user_firstname, ' ', user_lastname) LIKE ? OR CONCAT(user_lastname, ' ', user_firstname) LIKE ?)";
                $params = array_fill(0, 6, $search);
            }

            if (!empty($criteria['status'])) {
                $where[] = "work_status = ?";
                $params[] = $criteria['status'];
            }

            if (!empty($criteria['user_id'])) {
                $where[] = "work_user = ?";
                $params[] = $criteria['user_id'];
            }

            if (!empty($criteria['client_name'])) {
                $where[] = "client_name LIKE ?";
                $params[] = '%' . $criteria['client_name'] . '%';
            }

            if (!empty($criteria['project_id'])) {
                $where[] = "project_id = ?";
                $params[] = $criteria['project_id'];
            }

            if (!empty($criteria['week'])) {
                $where[] = "WEEK(work_week, 1) = ?";
                $params[] = $criteria['week'];
            }

            if (!empty($criteria['year'])) {
                $where[] = "YEAR(work_week) = ?";
                $params[] = $criteria['year'];
            }
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération du nombre de pages : " . $e->getMessage());
        }
    }
}
