<?php

namespace Src\Models;
/**
 * Classe représentant un utilisateur.
 * Hérite de la classe de base BaseModel.
 */
class Week
{
    /**
     * @var Work[]|null
     */
    private ?array $works;
    private ?int $year;
    private ?int $week;
    private ?int $project;

    /**
     * Constructeur qui initialise les propriétés avec des valeurs par défaut.
     *
     * @param mixed $data Un tableau associatif avec les colonnes et leurs valeurs.
     */
    public function __construct($data = null)
    {
        $this->setWorks(null);
        $this->setYear(null);
        $this->setWeek(null);
        $this->setProject(null);

        $this->hydrate($data);
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
                $methodName = "set" . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->{$methodName}($value);
                }
            }
        }
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- WORKS -----
    public function getWorks(): ?array
    {
        return $this->works;
    }
    public function setWorks(?array $works): void
    {
        $this->works = $works;
    }
    public function addWorks(?Work $work): void
    {
        if (is_null($this->works)) {
            $this->works = [];
        }
        if (!is_null($work)) {
            $this->works[] = $work;
        }
    }

    // ----- WEEK -----
    public function setWeek(?int $value): void
    {
        $this->week = ($value > 0 && $value <= 60) ? $value : null;
    }

    public function getWeek(bool $raw = false)
    {
        if (!is_null($this->week)) {
            return $raw ? $this->week : htmlspecialchars($this->week);
        }
        return 0;
    }

    // ----- YEAR -----
    public function setYear(?int $value): void
    {
        $this->year = ($value > 2020 && $value <= 2120) ? $value : null;
    }

    public function getYear(bool $raw = false)
    {
        if (!is_null($this->year)) {
            return $raw ? $this->year : htmlspecialchars($this->year);
        }
        return 0;
    }

    // ----- PROJECT -----
    public function setProject(?int $value): void
    {
        $this->project = $value >= 0 ? $value : 0;
    }
    public function getProject(bool $raw = false)
    {
        if (!is_null($this->project)) {
            return $raw ? $this->project : htmlspecialchars($this->project);
        }
        return 0;
    }
}
