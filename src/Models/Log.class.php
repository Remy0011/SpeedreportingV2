<?php

namespace Src\Models;

/**
 * Classe représentant un log.
 * Hérite de la classe de base BaseModel.
 */
class Log extends BaseModel
{
    protected static string $table = 'log';
    private ?string $action;
    private ?string $detail;
    private ?string $date;
    private ?int $user;

    public function getColNames(): array
    {
        return [
            'id',
            'action',
            'detail',
            'date',
            'user'
        ];
    }

    public function __construct($data = null)
    {
        $this->setId(0);
        $this->setAction(null);
        $this->setDetail(null);
        $this->setDate(null);
        $this->setUser(0);

        $this->hydrate($data);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- ACTION -----
    public function setAction(?string $value): void
    {
        $this->action = $value;
    }

    public function getAction(bool $raw = false): string
    {
        if (!is_null($this->action)) {
            return $raw ? $this->action : htmlspecialchars($this->action);
        }
        return '';
    }

    // ----- DETAIL -----
    public function setDetail(?string $value): void
    {
        $this->detail = $value;
    }

    public function getDetail(bool $raw = false): string
    {
        if (!is_null($this->detail)) {
            return $raw ? $this->detail : htmlspecialchars($this->detail);
        }
        return '';
    }

    // ----- DATE -----
    public function setDate(?string $value): void
    {
        $this->date = $value === '' ? null : $value;
    }

    public function getDate(bool $raw = false): string|null
    {
        if (!is_null($this->date)) {
            return $raw ? $this->date : htmlspecialchars($this->date);
        }
        return null;
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
