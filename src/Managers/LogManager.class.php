<?php

namespace Src\Managers;

/**
 * Clasee pour gérer les rôles dans la base de données.
 * Elle hérite de la classe de base BaseManager.
 */
class LogManager extends BaseManager
{
    protected static ?string $table = 'log';

    /**
     * Permet de récupérer les dernières actions (admin/user)
     * @param int $limit
     * @throws \Exception
     * @return array
     */
    public function getRecentLog(int $limit = 30, ?int $userId = null): array
    {
        $query = "SELECT log_action, log_detail, log_date
              FROM table_log";

        if ($userId !== null) {
            $query .= " WHERE log_user = :log_user";
        }

        $query .= " ORDER BY log_date DESC
                LIMIT :limit;";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            if ($userId !== null) {
                $stmt->bindValue(':log_user', $userId, \PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des logs récents : " . $e->getMessage());
        }
    }
}
