<?php

namespace Src\Managers;

class CardManager extends BaseManager
{
    protected static ?string $table = null;

    /* ====== WORK ====== */
    /**
     * Récupère le volume de travail hebdomadaire pour un utilisateur ou tous les utilisateurs.
     * Cette méthode permet de récupérer le volume de travail pour un utilisateur spécifique
     * ou pour tous les utilisateurs sur une période de semaines définie.
     * @param int|null $user_id L'ID de l'utilisateur pour lequel récupérer le volume de travail.
     * @param int $weeks_count Le nombre de semaines à récupérer, par défaut 12.
     * @param int|null $end_week La semaine de fin pour la récupération des données, par défaut la semaine actuelle.
     * @param int|null $end_year L'année de fin pour la récupération des données, par défaut l'année actuelle.
     * 
     * @return array Un tableau associatif contenant les semaines, années et le total d'heures travaillées.
     */
    public function getWeeklyWorkVolume(?int $user_id = null, int $weeks_count = 12, ?int $end_week = null, ?int $end_year = null): array
    {
        $query = "SELECT work_week, work_year, SUM(work_count) AS total_hours
        FROM table_work
        WHERE work_status = 'confirme'
        ";

        $params = [];

        if ($user_id !== null) {
            $query .= " AND work_user = ?";
            $params[] = $user_id;
        }

        $end_week ??= (int) date('W');
        $end_year ??= (int) date('o');

        $weeks = [];
        for ($i = 0; $i < $weeks_count; $i++) {
            $week = ($end_week - $i) % 52;
            if ($week <= 0) {
                $week += 52;
                $year = $end_year - 1;
            } else {
                $year = $end_year;
            }
            $weeks[] = ['week' => $week, 'year' => $year];
        }

        // Build WHERE clause for (work_week, work_year) pairs
        $wherePairs = [];
        foreach ($weeks as $pair) {
            $wherePairs[] = '(work_week = ? AND work_year = ?)';
            $params[] = $pair['week'];
            $params[] = $pair['year'];
        }
        $whereClause = implode(' OR ', $wherePairs);

        $query .= " AND ($whereClause)
            GROUP BY work_week, work_year
            ORDER BY work_year ASC, work_week ASC";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des données : " . $e->getMessage());
        }
    }

    /**
     * Récupère le volume de travail hebdomadaire pour un utilisateur spécifique.
     * Cette méthode est un alias pour getWeeklyWorkVolume avec l'ID de l'utilisateur spécifié.
     * @param int $userId L'ID de l'utilisateur pour lequel récupérer le volume de travail.
     * @param int $weeks_count Le nombre de semaines à récupérer, par défaut 12.
     * @param int|null $end_week La semaine de fin pour la récupération des données, par défaut la semaine actuelle.
     * @param int|null $end_year L'année de fin pour la récupération des données, par défaut l'année actuelle.
     * 
     * @return array Un tableau associatif contenant les semaines, années et le total d'heures travaillées.
     */
    public function getUserWeeklyWorkVolume(int $userId, int $weeks_count = 12, ?int $end_week = null, ?int $end_year = null): array
    {
        return $this->getWeeklyWorkVolume($userId, $weeks_count, $end_week, $end_year);
    }

    /**
     * Récupère le volume de travail hebdomadaire pour tous les utilisateurs.
     * Cette méthode permet de récupérer le volume de travail pour tous les utilisateurs
     * sur une période de semaines définie.
     * @param int $weeks_count Le nombre de semaines à récupérer, par défaut 12.
     * @param int|null $end_week La semaine de fin pour la récupération des données, par défaut la semaine actuelle.
     * @param int|null $end_year L'année de fin pour la récupération des données, par défaut l'année actuelle.
     *
     * @return array Un tableau associatif contenant les semaines, années et le total d'heures travaillées pour chaque utilisateur (convertit en int).
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

    /* ====== PROJECT ====== */
    /**
     * Récupère le nombre de projets par statut.
     * Cette méthode permet de récupérer le nombre de projets regroupés par leur statut.
     * 
     * @return array Un tableau associatif contenant les statuts de projet et le nombre de projets pour chaque statut.
     */
    public function getProjectStatusCounts(): array
    {
        $query = "SELECT project_status, COUNT(project_id) AS status_count
        FROM table_project
        GROUP BY project_status;
        ";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des données : " . $e->getMessage());
        }
    }

    /**
     * Récupère les projets importants.
     * Cette méthode récupère les projets en cours dont la date de fin est supérieure ou égale à la date actuelle,
     * et limite le résultat aux 5 premiers projets.
     * 
     * @return array Un tableau associatif contenant les projets importants.
     */
    public function getImportantProjects(): array
    {
        $query = "SELECT p.project_id, p.project_name, p.project_start, p.project_end, p.project_status
        FROM table_project p
        WHERE p.project_status = 'en_cours'
          AND p.project_end >= CURDATE()
        ORDER BY p.project_end ASC
        LIMIT 5;
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /* ====== CLIENTS ====== */
    /**
     * Récupère le nombre de projets par type de client.
     * Cette méthode permet de récupérer le nombre de projets regroupés par leur type de client.
     * 
     * @return array Un tableau associatif contenant les types de clients et le nombre de projets pour chaque type.
     */
    public function getProjectCountByClientType(): array
    {
        $query = "SELECT c.client_type, COUNT(p.project_id) AS project_count
        FROM table_project p
        LEFT JOIN table_client c ON p.project_client = c.client_id
        GROUP BY c.client_type
        ORDER BY c.client_type ASC;
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le nombre de projets par client.
     * Cette méthode permet de récupérer le nombre de projets regroupés par leur client.
     * 
     * @return array Un tableau associatif contenant les clients et le nombre de projets pour chaque client.
     */
    public function getProjectCountByClient(): array
    {
        $query = "SELECT c.client_name, COUNT(p.project_id) AS project_count
        FROM table_project p
        LEFT JOIN table_client c ON p.project_client = c.client_id
        GROUP BY c.client_name
        ORDER BY c.client_name ASC;
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /* ====== USERS ====== */
    /**
     * Récupère les utilisateurs ayant des pauses.
     * Cette méthode récupère les utilisateurs qui ont enregistré au moins une pause
     * dans la table des travaux, en filtrant ceux qui n'ont pas de projet associé
     * (avec work_project = 0).
     * 
     * @return array Un tableau associatif contenant les utilisateurs et le nombre de pauses pour chaque utilisateur.
     */
    public function getHaveBreakUser(): array
    {
        $query = "SELECT u.user_id, u.user_firstname, u.user_lastname, COUNT(w.work_id) AS break_count
        FROM table_user u
        INNER JOIN table_work w ON u.user_id = w.work_user
        WHERE w.work_project = 0
        GROUP BY u.user_id, u.user_firstname, u.user_lastname
        HAVING break_count > 0;
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les heures de travail par projet pour un utilisateur spécifique.
     * Cette méthode permet de récupérer le total des heures travaillées par un utilisateur
     * pour chaque projet, en filtrant les travaux confirmés.
     * @param int $user_id L'ID de l'utilisateur pour lequel récupérer les heures de travail par projet.
     * 
     * @return array Un tableau associatif contenant les projets et le total des heures travaillées pour chaque projet.
     */
    public function getUserProjectHours(int $user_id): array
    {
        $query = "SELECT p.project_id, p.project_name, SUM(w.work_count) AS total_hours
        FROM table_work w
        JOIN table_project p ON w.work_project = p.project_id
        WHERE w.work_user = ? 
          AND w.work_status = 'confirme'
        GROUP BY p.project_id, p.project_name
        ORDER BY total_hours DESC;
        ";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des projets utilisateur : " . $e->getMessage());
        }
    }
}
