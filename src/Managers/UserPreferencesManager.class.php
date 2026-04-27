<?php

namespace Src\Managers;

class UserPreferencesManager extends BaseManager
{

    /**
     * Enregistre une préférence utilisateur dans la base de données.
     * Si la préférence existe déjà, elle est mise à jour.
     * @param int $userId L'ID de l'utilisateur.
     * @param string $key La clé de la préférence.
     * @param string $value La valeur de la préférence.
     * 
     * @return bool True si l'enregistrement a réussi, sinon false.
     */
    public function savePreference(int $userId, string $key, string $value): bool
    {
        $query = "INSERT INTO table_user_preferences (user_id, key_name, value)
        VALUES (:user_id, :key_name, :value)
        ON DUPLICATE KEY UPDATE value = :value
        ";

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            'user_id' => $userId,
            'key_name' => $key,
            'value' => $value
        ]);
    }

    /**
     * Récupère les préférences d'un utilisateur à partir de la base de données.
     * @param int $userId L'ID de l'utilisateur.
     * 
     * @return array Un tableau associatif des préférences (clé => valeur).
     */
    public function getPreferencesByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT key_name, value FROM table_user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);

        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'value', 'key_name');
    }
}
