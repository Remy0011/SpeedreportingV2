<?php

namespace Src\Models;

use Src\Models\Enums\Status\UserStatus;
/**
 * Classe représentant un utilisateur.
 * Hérite de la classe de base BaseModel.
 */
class Week
{
    private ?User $user;
    private ?int $week;
    private ?int $year;

    /**
     * @var array|null Tableau contenant des tableaux associatifs avec les clés 'work', 'user', 'project'.
     * Chaque élément est de la forme : ['work' => Work, 'user' => User, 'project' => Project]
     */
    private ?array $data;

    /**
     * Constructeur qui initialise les propriétés avec des valeurs par défaut.
     *
     * @param mixed $data Un tableau associatif avec les colonnes et leurs valeurs.
     */
    public function __construct($data = null)
    {
        $this->setUser(null);
        $this->setWeek(null);
        $this->setYear(null);
        $this->setData(null);

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

    public function getStartToEnd(bool $day = true, bool $month = true, bool $year = true, bool $bx = true){
        $start = $this->getStart($day, $month, $year);
        $end = $this->getEnd($day, $month, $year);
        return $start . ($bx ? "<i class='bx  bx-caret-right'></i>" : " - ") . $end;
    }

    public function getStart(bool $day = true, bool $month = true, bool $year = true){
        $date = new \DateTime();
        $date->setISODate($this->getYear(), $this->getWeek());
        $format = array_filter([
            $day ? 'd' : null,
            $month ? 'm' : null,
            $year ? 'Y' : null
        ]);
        $format_str = implode('/', $format);
        return $date->format($format_str);
    }

    public function getEnd(bool $day = true, bool $month = true, bool $year = true){
        $date = new \DateTime();
        $date->setISODate($this->getYear(), $this->getWeek());
        $date->modify('+6 days');
        $format = array_filter([
            $day ? 'd' : null,
            $month ? 'm' : null,
            $year ? 'Y' : null
        ]);
        $format_str = implode('/', $format);
        return $date->format($format_str);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- USER -----
    public function setUser(?User $value): void
    {
        $this->user = $value;
    }

    public function getUser(bool $raw = false): ?User
    {
        if (!is_null($this->user)) {
            return $this->user;
        }
        $return_user = new User();
        $return_user->setFirstname('Utilisateur');
        $return_user->setLastname('Inconnu');
        return $return_user;
    }

    // ----- WEEK -----
    public function setWeek(?int $value): void
    {
        $this->week = $value;
    }
    public function getWeek(bool $raw = false): int
    {
        if (!is_null($this->week)) {
            return $raw ? $this->week : htmlspecialchars($this->week);
        }
        return 0;
    }
    // ----- YEAR -----
    public function setYear(?int $value): void
    {
        $this->year = $value;
    }
    public function getYear(bool $raw = false): int
    {
        if (!is_null($this->year)) {
            return $raw ? $this->year : htmlspecialchars($this->year);
        }
        return 0;
    }

    // ----- DATA -----
    /**
     * Définit le tableau des entrées de la semaines.
     *
     * @param array|null $value Un tableau associatif avec les colonnes et leurs valeurs.
     *                          Chaque élément est de la forme : ['work' => Work, 'user' => User, 'project' => Project]
     */
    public function setData(?array $value): void
    {
        $this->data = $value;
    }

    /**
     * Retourne le tableau des travaux de la semaine.
     *
     * @return array|null Un tableau associatif avec les colonnes et leurs valeurs.
     *                   Chaque élément est de la forme : ['work' => Work, 'user' => User, 'project' => Project]
     */
    public function getData(): array
    {
        return $this->data ?? [
            'work' => null,
            'user' => null,
            'project' => null,
        ];
    }

    public function addData(array $data): void
    {
        if (is_null($this->data)) {
            $this->data = [];
        }
        $this->data[] = $data;
    }

    public function getCount(): float
    {
        $total = 0;
        foreach ($this->data as $data) {
            $total += $data['work']->getCount();
        }
        return $total;
    }

    public function getEntriesCount(): int
    {
        return count($this->data);
    }

    public function getIds(bool $toString = false): array|string
    {
        $ids = [];
        foreach ($this->data as $data) {
            $ids[] = $data['work']->getId();
        }

        if ($toString) {
            return implode(',', $ids);
        }

        return $ids;
    }
}
