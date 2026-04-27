<?php

namespace Src\Models;

/**
 * Classe représentant un rôle.
 * Hérite de la classe de base BaseModel.
 */
class Role extends BaseModel
{
    protected static string $table = 'role';
    private ?string $name;
    private ?string $fr;
    private ?string $description;

    public function getColNames(): array
    {
        return [
            'id',
            'name',
            'fr',
            'description'
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
        $this->setFr(null);
        $this->setDescription(null);

        $this->hydrate($data);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- NAME -----
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    // ----- FR -----
    public function getFr(): ?string
    {
        return $this->fr;
    }

    public function setFr(?string $fr): void
    {
        $this->fr = $fr;
    }

    // ----- DESCRIPTION -----
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
