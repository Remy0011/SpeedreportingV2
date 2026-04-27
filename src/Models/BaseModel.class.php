<?php

namespace Src\Models;

/**
 * Classe abstraite représentant un modèle de base pour les entités de la base de données.
 */
abstract class BaseModel {
    private ?int $id = 0;
    private ?string $creation = null;
    private ?string $last = null;
    
    /**
     * Préfixe utilisé pour identifier les clés à supprimer (peut être redéfini dans les classes filles)
     *
     * @var string
     */
    protected static string $table = '';

    /**
     * Renvoie la liste des noms des colonnes de la table.
     * Non préfixées par le nom de la table.
     *
     * @return array Liste des colonnes de la table, sans préfix.
     * @throws \Exception Si la méthode n'est pas implémentée dans la classe fille.
     */
    abstract public function getColNames(): array;

    /**
     * Renvoie la liste des noms des colonnes de la table.
     * Préfixées par le nom de la table.
     * 
     * @return array Liste des colonnes de la table, avec préfix.
     */
    public function getColNamesFull(): array
    {
        return array_map(fn($col) => "{$this::$table}_{$col}", $this->getColNames());
    }

    /**
     * Hydrate l'objet à partir d'un tableau associatif.
     *
     * @param mixed $data Un tableau associatif avec les colonnes et leurs valeurs.
     */
    public function hydrate($data = null): void
    {
        if (!is_null($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                // Si un préfixe est défini, on le retire de la clé
                if (!empty(static::$table)) {
                    $key = str_replace(static::$table . '_', "", $key);
                }
                $methodName = "set" . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->{$methodName}($value);
                }
            }
        }
    }

    // ----- ID -----
    public function setId(?int $value): void
    {
        $this->id = $value >= 0 ? $value : 0;
    }

    public function getId(bool $raw = false)
    {
        if (!is_null($this->id)) {
            return $raw ? $this->id : htmlspecialchars($this->id);
        }
        return 0;
    }

    // ----- LAST -----
    protected function setLast(?string $value): void
    {
        $this->last = $value === '' ? null : $value;
    }
    public function getLast(bool $raw = false): string|null
    {
        if (!is_null($this->last)) {
            return $raw ? $this->last : htmlspecialchars($this->last);
        }
        return null;
    }

    // ----- CREATION -----
    public function setCreation(?string $value): void
    {
        $this->creation = $value === '' ? null : $value;
    }
    public function getCreation(bool $raw = false): string|null
    {
        if (!is_null($this->creation)) {
            $value = (new \DateTime($this->creation))->format('d/m/Y');
            return $raw ? $value : htmlspecialchars($value);
        }
        return null;
    }

}
