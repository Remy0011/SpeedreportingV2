<?php

namespace Src\Models;

use Src\Models\Enums\Status\UserStatus;
/**
 * Classe représentant un utilisateur.
 * Hérite de la classe de base BaseModel.
 */
class User extends BaseModel
{

    protected static string $table = 'user';
    private ?string $email;
    private ?string $firstname;
    private ?string $lastname;
    private ?string $picture;
    private ?string $status;
    private ?int $role;

    public function getColNames(): array
    {
        return [
            'id',
            'email',
            'firstname',
            'lastname',
            'picture',
            'status',
            'role'
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
        $this->setLastname(null);
        $this->setFirstname(null);
        $this->setEmail(null);
        $this->setRole(0);
        $this->setStatus(null);
        $this->setPicture(null);

        $this->hydrate($data);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- CUSTOM ------

    public function getName(): string
    {
        if (!is_null($this->firstname) && !is_null($this->lastname)) {
            return htmlspecialchars($this->firstname . ' ' . $this->lastname);
        }
        return '';
    }

    // ----- EMAIL -----
    public function setEmail(?string $value): void
    {
        $this->email = $value;
    }

    public function getEmail(bool $raw = false): string
    {
        if (!is_null($this->email)) {
            return $raw ? $this->email : htmlspecialchars($this->email);
        }
        return '';
    }

    // ----- ROLE -----
    public function setRole(?int $value): void
    {
        $this->role = $value >= 0 ? $value : 0;
    }

    public function getRole(bool $raw = false)
    {
        if (!is_null($this->role)) {
            return $raw ? $this->role : htmlspecialchars($this->role);
        }
        return 0;
    }

    // ----- FIRSTNAME -----
    public function setFirstname(?string $value): void
    {
        $this->firstname = $value;
    }

    public function getFirstname(bool $raw = false): string
    {
        if (!is_null($this->firstname)) {
            return $raw ? $this->firstname : htmlspecialchars($this->firstname);
        }
        return '';
    }

    // ----- LASTNAME -----
    public function setLastname(?string $value): void
    {
        $this->lastname = $value;
    }

    public function getLastname(bool $raw = false): string
    {
        if (!is_null($this->lastname)) {
            return $raw ? $this->lastname : htmlspecialchars($this->lastname);
        }
        return '';
    }

    // ----- PICTURE -----
    public function setPicture(?string $value): void
    {
        $this->picture = $value;
    }

    public function getPicture(bool $raw = false): string
    {
        if (!is_null($this->picture)) {
            return $raw ? $this->picture : htmlspecialchars($this->picture);
        }
        return '';
    }

    // ----- STATUS -----
    public function setStatus(?string $value): void
    {
        $this->status = $value;
        if (UserStatus::isValidValue($value)) {
            $this->status = $value;
        } else {
            $this->status = UserStatus::getDefault();
        }
    }

    public function getStatus(bool $fr = false, bool $raw = false): string
    {
        if (!is_null($this->status)) {
            if ($fr) {
                $return = UserStatus::getEnumOptions()[$this->status] ?? 'État inconnu';
            } else {
                $return = $this->status;
            }
            return $raw ? $return : htmlspecialchars($return);
        }
        return UserStatus::getDefault();
    }
}
