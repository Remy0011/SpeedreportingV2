<?php

namespace Src\Managers;

/**
 * Classe pour gérer les utilisateurs dans la base de données.
 * Elle hérite de la classe de base BaseManager.
 */
class UserManager extends BaseManager
{
    protected static ?string $table = 'user';

    /**
     * Vérifie la correspondance entre l'email et le mot de passe d'un utilisateur dans la base de données.
     * 
     * @param string $email L'email de l'utilisateur.
     * @param string $password Le mot de passe de l'utilisateur.
     * 
     * @return mixed Les informations de l'utilisateur si la connexion est réussie, sinon false.
     */
    public function authenticate(string $email, string $password): mixed
    {
        $stmt = $this->pdo->prepare("SELECT user_id, user_password, user_role FROM table_{$this::$table} WHERE user_email = :user_email AND user_password IS NOT NULL");
        $stmt->execute([':user_email' => $email]);

        $user_row = $stmt->fetch();
        if ($user_row && password_verify($password, $user_row['user_password'])) {
            return $user_row;
        }

        return false;
    }

    /**
     * Change le mot de passe d'un utilisateur dans la base de données.
     * 
     * @param int $user_id L'ID de l'utilisateur.
     * @param string $new_password Le nouveau mot de passe de l'utilisateur.
     * 
     * @return bool True si le mot de passe a été changé avec succès, sinon false.
     */
    public function changePassword(int $user_id, string $new_password): bool
    {
        $stmt = $this->pdo->prepare("UPDATE table_{$this::$table} SET user_password = :user_password, user_status = 'confirme' WHERE user_id = :user_id");
        return $stmt->execute([
            ':user_password' => password_hash($new_password, PASSWORD_DEFAULT),
            ':user_id' => $user_id,
        ]);
    }

    /**
     * Supprime le token d'un utilisateur dans la base de données.
     * @param int $user_id L'ID de l'utilisateur.
     * @param string $token Le token à supprimer.
     * 
     * @return bool True si le token a été supprimé avec succès, sinon false.
     */
    public function deleteUserToken(int $user_id, string $token): bool
    {
        $query = "UPDATE table_{$this::$table} SET user_token = NULL WHERE user_id = :user_id AND user_token = :token";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':token' => $token
        ]);
    }

    /**
     * Cette méthode est utilisée pour obtenir les informations du premier utilisateur ayant le rôle d'administrateur.
     * 
     * @return array|null Les informations de l'utilisateur administrateur ou null si aucun utilisateur n'est trouvé.
     */
    public function getAdminUser()
    {
        $query = "SELECT user_id, user_firstname, user_lastname, user_email, user_role 
        FROM table_{$this::$table}
        WHERE user_role = '1'
        LIMIT 1";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $admin_user = $stmt->fetch();

        if ($admin_user) {
            return $admin_user;
        }

        return null;
    }

    /**
     * Récupère un utilisateur par son email.
     * @param string $email L'email de l'utilisateur.
     * @param array|null $cols Les colonnes à sélectionner, ou null pour toutes les colonnes.
     * 
     * @return array|null Les informations de l'utilisateur ou null si aucun utilisateur n'est trouvé.
     */
    public function getUserByEmail(string $email, ?array $cols = null)
    {
        $columns = $cols ? implode(',', $cols) : '*';
        $query = "SELECT {$columns} FROM table_{$this::$table} WHERE user_email = :user_email";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':user_email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            return $user;
        }

        return null;
    }

    /**
     * Récupère les données pour la table des utilisateurs avec pagination et filtres.
     * @param int $page Numéro de la page à récupérer (1 par défaut).
     * @param int $limit Nombre de résultats par page (10 par défaut).
     * @param array|null $filters Filtres optionnels pour la recherche (par email, nom, statut, rôle).
     * 
     * @return array Les données des utilisateurs avec les rôles associés.
     */
    public function getTableData(int $page = 1, int $limit = 10, ?array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $query = "SELECT user_id, user_email, user_firstname, user_lastname, user_picture, user_creation, 
                     user_status, user_role, 
                     role_id, role_name, role_fr, role_description
              FROM table_user
              LEFT JOIN table_role ON table_user.user_role = table_role.role_id
              WHERE user_id != 0";

        $params = [];

        // Recherche globale
        if (!empty($filters['search'])) {
            $query .= " AND (user_email LIKE :search OR user_firstname LIKE :search OR user_lastname LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Filtre par statut
        if (!empty($filters['status'])) {
            $query .= " AND user_status = :status";
            $params[':status'] = $filters['status'];
        }

        // Filtre par rôle
        if (!empty($filters['role_id'])) {
            $query .= " AND user_role = :role_id";
            $params[':role_id'] = $filters['role_id'];
        }

        $query .= " LIMIT $limit OFFSET $offset";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre d'utilisateurs dans la base de données avec des filtres optionnels.
     * 
     * @param array|null $filters Filtres optionnels pour la recherche (par email, nom, statut, rôle).
     * 
     * @return int Le nombre d'utilisateurs correspondant aux critères.
     */
    public function getTableCount(?array $filters = []): int
    {
        $query = "SELECT COUNT(user_id) AS count 
              FROM table_user 
              WHERE user_id != 0";

        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (user_email LIKE :search OR user_firstname LIKE :search OR user_lastname LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $query .= " AND user_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['role_id'])) {
            $query .= " AND user_role = :role_id";
            $params[':role_id'] = $filters['role_id'];
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors du comptage des utilisateurs : " . $e->getMessage());
        }
    }

    /**
     * Supprime un utilisateur de la base de données.
     * @param int $user_id L'ID de l'utilisateur à supprimer.
     * 
     * @return bool True si l'utilisateur a été supprimé avec succès, sinon false.
     */
    public function deleteUser(int $user_id): bool
    {
        $query = "DELETE FROM table_{$this::$table} WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([':user_id' => $user_id]);
    }

    /**
     * Stocke le token d'un utilisateur dans la base de données.
     * @param int $user_id L'ID de l'utilisateur.
     * @param string $token Le token à stocker.
     * 
     * @return bool True si le token a été stocké avec succès, sinon false.
     */
    public function storeUserToken(int $user_id, string $token): bool
    {
        $query = "UPDATE table_user
        SET user_token = :token
        WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':token' => $token
        ]);
    }

    /**
     * Vérifie si le token d'un utilisateur est valide.
     * @param int $user_id L'ID de l'utilisateur.
     * @param string $token Le token à vérifier.
     * 
     * @return bool True si le token est valide, sinon false.
     */
    public function verifyUserToken(int $user_id, string $token): bool
    {
        $query = "SELECT user_token FROM table_user WHERE user_id = :user_id AND user_token = :token";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':token' => $token
        ]);
        return $stmt->fetchColumn() !== false;
    }
}
