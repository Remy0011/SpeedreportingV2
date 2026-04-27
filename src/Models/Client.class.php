<?php

namespace Src\Models;

use Src\Models\Enums\Type\ClientType;

/**
 * Classe représentant un client.
 * Hérite de la classe de base BaseModel.
 */
class Client extends BaseModel
{
    protected static string $table = 'client';
    private ?string $name;
    private ?string $type;

    public function getColNames(): array
    {
        return [
            'id',
            'name',
            'type'
        ];
    }

    /**
     * Constructeur qui initialise les propriétés avec des valeurs par défaut.
     *
     * @param mixed $data Un tableau associatif avec les colonnes et leurs valeurs.
     */
    public function __construct($data = null)
    {
        $this->setId(0);
        $this->setName(null);

        $this->hydrate($data);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- NAME -----
    public function setName(?string $value): void
    {
        $this->name = $value;
    }

    public function getName(bool $raw = false): string
    {
        if (!is_null($this->name)) {
            return $raw ? $this->name : htmlspecialchars($this->name);
        }
        return '';
    }

    // ----- TYPE -----
    public function setType(?string $value): void
    {
        $this->type = $value;
        if (ClientType::isValidValue($value)) {
            $this->type = $value;
        } else {
            $this->type = ClientType::getDefault();
        }
    }

    public function getType(bool $raw = false): string
    {
        if (!is_null($this->type)) {
            return $raw ? $this->type : htmlspecialchars($this->type);
        }
        return ClientType::getDefault();
    }
}
