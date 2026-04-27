<?php

namespace Src\Managers;

use Src\Models\BaseModel;
use PDO;
use PDOException;

/**
 * Classe de base pour gérer les opérations CRUD sur une table de la base de données.
 * Elle utilise PDO pour interagir avec la base de données.
 * Classe abstraite, doit être étendue par des classes spécifiques pour chaque table.
 */
abstract class BaseManager
{
    protected ?PDO $pdo;
    protected static ?string $table = '';

    public function __construct()
    {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $dbname = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');


        try {
            $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Permets d'executer une requête SQL.
     * 
     * @param string $query La requête SQL à exécuter.
     * @param array $params Les paramètres à lier à la requête.
     * Exemple : ['param1' => 'value1', 'param2' => 'value2']
     * 
     * @return mixed
     */
    public function execute(string $query, array $params = []): mixed
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        }
    }

    // CRUD Methods

    /**
     * Sauvegarde un élement dans la base de données.
     * Insère un nouvel élement si son ID est 0, sinon met à jour l'existant.
     * 
     * @param BaseModel $model Le ride à sauvegarder
     */
    public function save(BaseModel $model)
    {
        switch ($model->getId()) {
            case 0:
                $this->insert($model);
                break;
            default:
                $this->update($model);
        }
    }

    // Create

    // ---------- CREATE ----------

    /**
     * Insère un nouvel élément dans la base de données.
     * 
     * @param BaseModel $model L'élément à insérer
     * @return int L'ID de l'élément inséré
     */
    private function insert(BaseModel $model)
    {
        $fields = $model->getColNames();
        $methods = array_map(fn($field) => 'get' . ucfirst($field), $fields);

        $cols = $model->getColNamesFull();
        $placeholders = implode(', ', array_map(fn($col) => ":$col", $cols));
        $columns = implode(', ', $cols);

        $query = "INSERT INTO table_{$this::$table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->pdo->prepare($query);
            $values = [];

            foreach ($methods as $index => $method) {
                if (method_exists($model, $method)) {
                    $values[":{$cols[$index]}"] = $model->$method(raw: true);
                }
            }

            $stmt->execute($values);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'insertion : " . $e->getMessage());
        }
    }

    // ---------- READ ----------

    /**
     * Récupère tous les éléments de la base de données avec des options de filtrage, de tri, de pagination et de jointure.
     * 
     * @param array|null $col Les colonnes à sélectionner. Si null ou vide, toutes les colonnes seront sélectionnées.
     * @param array|null $criteria Les critères de filtrage sous forme de tableau associatif. Si null, aucun filtre n'est appliqué.
     * @param int|null $limit Le nombre maximum de résultats à retourner. Si null, aucun limite n'est appliquée.
     * @param int|null $offset Le décalage à appliquer pour la pagination. Si null, aucun décalage n'est appliqué.
     * @param string|null $orderBy La colonne par laquelle trier les résultats. Si null, aucun tri n'est appliqué.
     * @param string $direction La direction du tri (ASC pour croissant, DESC pour décroissant). Par défaut, 'ASC'.
     * @param array|null $joins Les jointures à appliquer sous forme de tableau associatif. Exemple : ['other_table' => 'this_table.column = other_table.column']
     * 
     * @return array Un tableau contenant les résultats de la requête sous forme associative.
     * 
     * @throws \Exception Si une erreur survient lors de l'exécution de la requête.
     */
    public function fetchAll(?array $col = null, ?array $criteria = null, ?int $limit = null, ?int $offset = null, ?string $orderBy = null, string $direction = 'ASC', ?array $joins = null): array
    {
        $columns = is_array($col) && !empty($col) ? implode(", ", array_map('trim', $col)) : '*';
        $query = "SELECT {$columns} FROM table_{$this::$table}";
        $values = [];

        if (!empty($joins)) {
            foreach ($joins as $table => $condition) {
                $query .= " JOIN {$table} ON {$condition}";
            }
        }

        if (!empty($criteria)) {
            $where = implode(' AND ', array_map(fn($key) => "$key = :$key", array_keys($criteria)));
            $query .= " WHERE {$where}";
            $values = $criteria;
        }

        if ($orderBy) {
            $query .= " ORDER BY {$orderBy} {$direction}";
        }

        if ($limit !== null) {
            $query .= " LIMIT {$limit}";
        }

        if ($offset !== null) {
            $query .= " OFFSET {$offset}";
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        }
    }


    /**
     * Recherche une ligne dans la table associée en fonction de l'ID fourni.
     *
     * @param int $id L'identifiant unique de la ligne à rechercher.
     * @param array|null $col Les colonnes à sélectionner. Si null ou vide, toutes les colonnes seront sélectionnées.
     * 
     * @return array Un tableau contenant les résultats de la requête sous forme associative.
     * 
     * @throws \Exception Lance une exception en cas d'erreur lors de l'exécution de la requête SQL.
     */
    public function find(int $id, ?array $col = null): array|bool
    {
        $columns = is_array($col) && !empty($col) ? implode(", ", array_map('trim', $col)) : '*';
        $query = "SELECT {$columns} FROM table_{$this::$table} WHERE {$this::$table}_id = :id";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        }
    }

    /**
     * Récupère une ou plusieurs lignes de la table associée en fonction des critères fournis.
     *
     * @param array $criteria Les critères de recherche sous forme de tableau associatif.
     * @param array|null $col Les colonnes à sélectionner. Si null ou vide, toutes les colonnes seront sélectionnées.
     * 
     * @return array Un tableau contenant les résultats de la requête sous forme associative.
     * 
     * @throws \Exception Lance une exception en cas d'erreur lors de l'exécution de la requête SQL.
     */
    public function findBy(array $criteria, ?array $col = null): array|bool
    {
        $columns = is_array($col) && !empty($col) ? implode(", ", array_map('trim', $col)) : '*';
        $where = implode(' AND ', array_map(fn($key) => "$key = :$key", array_keys($criteria)));

        $query = "SELECT {$columns} FROM table_{$this::$table} WHERE {$where}";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($criteria);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        }
    }

    /**
     * Renvoie le nombre total d'éléments dans la table associée avec une clause WHERE optionnelle.
     * 
     * @param array|null $criteria Les critères de filtrage sous forme de tableau associatif. Si null, aucun filtre n'est appliqué.
     * 
     * @return int Le nombre total d'éléments correspondant aux critères.
     */
    public function count(?array $criteria = null): int
    {
        $query = "SELECT COUNT(*) FROM table_{$this::$table}";
        $values = [];

        if (!empty($criteria)) {
            $where = implode(' AND ', array_map(fn($key) => "$key = :$key", array_keys($criteria)));
            $query .= " WHERE {$where}";
            $values = $criteria;
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
            $count = $stmt->fetchColumn();
            return (int) $count;
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        }
    }


    // ---------- UPDATE ----------

    /**
     * Met à jour un élément existant dans la base de données.
     * 
     * @param BaseModel $model L'élément à mettre à jour
     * @return bool True si la mise à jour a réussi, False sinon
     * 
     * @throws \Exception Lance une exception en cas d'erreur lors de l'exécution de la requête SQL.
     */
    public function update(BaseModel $model): bool
    {
        $fields = $model->getColNames();
        $methods = array_map(fn($field) => 'get' . ucfirst($field), $fields);

        $cols = $model->getColNamesFull();

        $setClause = implode(', ', array_map(fn($col) => "$col = :$col", $cols));
        $query = "UPDATE table_{$this::$table} SET {$setClause} WHERE {$this::$table}_id = :id";

        try {
            $stmt = $this->pdo->prepare($query);
            $values = [':id' => $model->getId()];

            foreach ($methods as $index => $method) {
                if (method_exists($model, $method)) {
                    $values[":{$cols[$index]}"] = $model->$method(raw: true);
                }
            }

            return $stmt->execute($values);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    }
}
