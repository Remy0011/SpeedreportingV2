<?php

namespace Src\Models;

/**
 * Classe représentant un token.
 * Hérite de la classe de base BaseModel.
 */
class Token extends BaseModel
{
    protected static string $table = 'token';
    private ?string $value;
    private ?string $expiry;
    private ?int $type;
    private ?int $user;

    public function getColNames(): array
    {
        return [
            'id',
            'value',
            'expiry',
            'type',
            'user'
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
        $this->setValue(null);
        $this->setExpiry(null);
        $this->setType(0);
        $this->setUser(0);

        $this->hydrate($data);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- VALUE -----
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getValue(bool $raw = false): string
    {
        if (!is_null($this->value)) {
            return $raw ? $this->value : htmlspecialchars($this->value);
        }
        return '';
    }

    // ----- EXPIRY -----
    public function setExpiry(?string $value): void
    {
        $this->expiry = $value === '' ? null : $value;
    }

    public function getExpiry(bool $raw = false): string|null
    {
        if (!is_null($this->expiry)) {
            return $raw ? $this->expiry : htmlspecialchars($this->expiry);
        }
        return null;
    }

    // ----- TYPE -----
    public function setType(?int $value): void
    {
        $this->type = $value >= 0 ? $value : 0;
    }

    public function getType(bool $raw = false): int
    {
        if (!is_null($this->type)) {
            return $raw ? $this->type : htmlspecialchars($this->type);
        }
        return 0;
    }

    // ----- USER -----
    public function setUser(?int $value): void
    {
        $this->user = $value >= 0 ? $value : 0;
    }

    public function getUser(bool $raw = false): int
    {
        if (!is_null($this->user)) {
            return $raw ? $this->user : htmlspecialchars($this->user);
        }
        return 0;
    }
}
