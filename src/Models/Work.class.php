<?php

namespace Src\Models;

use PDO;
use Src\Models\Enums\Status\WorkStatus;
use Src\Models\Enums\Type\WorkType;

/**
 * Classe représentant un pointage d'heure.
 * Hérite de la classe de base BaseModel.
 */
class Work extends BaseModel
{

    protected static string $table = 'work';
    private ?float $count;
    private ?int $day;
    private ?int $week;
    private ?int $year;
    private ?string $description;
    private ?int $project;
    private ?int $user;
    private ?string $status;
    private ?string $type;

    public function getColNames(): array
    {
        return [
            'id',
            'count',
            'day',
            'week',
            'year',
            'description',
            'project',
            'user',
            'status'
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
        $this->setUser(null);
        $this->setProject(null);
        $this->setDay(null);
        $this->setWeek(null);
        $this->setYear(null);
        $this->setCount(null);
        $this->setDescription(null);
        $this->setStatus(null);

        $this->hydrate($data);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- DATE -----

    /**
     * Récupère la date au format ISO (YYYY-MM-DD).
     * Year et Week doivent être définis.
     * Day est optionnel.
     * 
     * @param bool $raw
     * @return string
     */
    public function getDate(bool $raw = false): string
    {
        if (!is_null($this->year) && !is_null($this->week)) {
            if (!is_null($this->day)) {
                $date = new \DateTime();
                $date->setISODate($this->year, $this->week, $this->day);
                $dateStr = $date->format('d/m/Y');
            } else {
                // If day is not set, return year-week (ISO format)
                $dateStr = sprintf('%04d-s%02d', $this->year, $this->week);
            }
            return $raw ? $dateStr : htmlspecialchars($dateStr);
        }
        return '';
    }

    /**
     * Récupère le mois (numéro du mois, 1-12).
     * Year, Week et Day doivent être définis.
     * 
     * @param bool $raw
     * @return int
     */
    public function getMonth(bool $raw = false): int
    {
        // day is ISO-8601 day of week (1=Monday, 7=Sunday)
        if (!is_null($this->year) && !is_null($this->week) && !is_null($this->day)) {
            $date = new \DateTime();
            $date->setISODate($this->year, $this->week, $this->day);
            $month = (int) $date->format('n');
            return $raw ? $month : (int) htmlspecialchars((string)$month);
        }
        return 0;
    }

    // ----- USER -----
    public function setUser(?int $value): void
    {
        $this->user = $value >= 0 ? $value : 0;
    }

    public function getUser(bool $raw = false)
    {
        if (!is_null($this->user)) {
            return $raw ? $this->user : htmlspecialchars($this->user);
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

    // ----- DAY -----
    public function setDay(?int $value): void
    {
        $this->day = ($value > 0 && $value <= 7) ? $value : null;
    }

    public function getDay(bool $raw = false)
    {
        if (!is_null($this->day)) {
            return $raw ? $this->day : htmlspecialchars($this->day);
        }
        return 0;
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

    // ----- COUNT -----
    public function setCount(?float $value): void
    {
        $this->count = $value >= 0 ? $value : 0.0;
    }

    public function getCount(bool $raw = false): float
    {
        if (!is_null($this->count)) {
            return $raw ? $this->count : (float) htmlspecialchars($this->count);
        }
        return 0.0;
    }

    // ----- DESCRIPTION -----
    public function setDescription(?string $value): void
    {
        $this->description = $value;
    }

    public function getDescription(bool $raw = false): string
    {
        if (!is_null($this->description)) {
            return $raw ? $this->description : htmlspecialchars($this->description);
        }
        return '';
    }

    // ----- STATUS -----
    public function setStatus(?string $value): void
    {
        $this->status = $value >= 0 ? $value : 0;
        if (WorkStatus::isValidValue($value)) {
            $this->status = $value;
        } else {
            $this->status = WorkStatus::getDefault();
        }
    }

    public function getStatus(bool $fr = false, bool $raw = false): string
    {
        if (!is_null($this->status)) {
            if ($fr) {
                $return = WorkStatus::getEnumOptions()[$this->status] ?? 'État inconnu';
            } else {
                $return = $this->status;
            }
            return $raw ? $return : htmlspecialchars($return);
        }
        return WorkStatus::getDefault();
    }

    // ----- TYPE -----
    public function setType(?string $value): void
    {
        $this->type = $value;
        if (!WorkType::isValidValue($value)) {
            $this->type = WorkType::getDefault();
        }
    }
    public function getType(bool $fr = false, bool $raw = false): string
    {
        if (!is_null($this->type)) {
            if ($fr) {
                $return = WorkType::getEnumOptions()[$this->type] ?? 'Type inconnu';
            } else {
                $return = $this->type;
            }
            return $raw ? $return : htmlspecialchars($return);
        }
        return WorkType::getDefault();
    }
}
