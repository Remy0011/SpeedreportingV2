<?php

namespace Src\Models;

use Src\Models\Enums\Status\ProjectStatus;

/**
 * Classe représentant un projet.
 * Hérite de la classe de base BaseModel.
 */
class Project extends BaseModel
{

    protected static string $table = 'project';
    private ?string $name;
    private ?string $start;
    private ?string $end;
    private ?string $realend;
    private ?string $description;
    private ?string $status;
    private ?float $resource;
    private ?int $dev;
    private ?int $client;

    public function getColNames(): array
    {
        return [
            'id',
            'name',
            'start',
            'end',
            'realend',
            'description',
            'status',
            'resource',
            'client',
            'dev'
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
        $this->setDescription(null);
        $this->setResource(null);
        $this->setStart(null);
        $this->setEnd(null);
        $this->setRealEnd(null);
        $this->setClient(null);
        $this->setCreation(null);
        $this->setStatus(null);
        $this->setDev(null);

        $this->hydrate($data);
    }

    /* ---------- GETTER & SETTER ---------- */

    // ----- UTILS -----
    public function getTimeProgression(): float
    {
        if (empty($this->start) || empty($this->end)) {
            return 0.0;
        }

        $start = strtotime($this->start);
        $end = strtotime($this->end);
        $now = time();
        $realEnd = $this->realend ? strtotime($this->realend) : null;

        if ($start === false || $end === false || $end <= $start) {
            return 0.0;
        }

        if ($realEnd) {
            $duration = $end - $start;
            $realDuration = $realEnd - $start;

            if ($realDuration <= 0) {
                return 100.0;
            }

            $progress = ($realDuration / $duration) * 100;

            return round($progress, 2);
        }

        if ($now <= $start) {
            return 0.0;
        }

        if ($now >= $end) {
            return 100.0;
        }

        $progress = (($now - $start) / ($end - $start)) * 100;
        return round($progress, 2);
    }

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

    // ----- START -----
    public function setStart(?string $value): void
    {
        $this->start = $value === '' ? null : $value;
    }

    public function getStart(bool $raw = false): string|null
    {
        if (!is_null($this->start)) {
            return $raw ? $this->start : htmlspecialchars($this->start);
        }
        return null;
    }

    // ----- END -----
    public function setEnd(?string $value): void
    {
        $this->end = $value === '' ? null : $value;
    }

    public function getEnd(bool $raw = false): string|null
    {
        if (!is_null($this->end)) {
            return $raw ? $this->end : htmlspecialchars($this->end);
        }
        return null;
    }

    // ----- REAL_END -----
    public function setRealEnd(?string $value): void
    {
        $this->realend = $value === '' ? null : $value;
    }
    public function getRealEnd(bool $raw = false): string|null
    {
        if (!is_null($this->realend)) {
            return $raw ? $this->realend : htmlspecialchars($this->realend);
        }
        return null;
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
        $this->status = $value;
        if (ProjectStatus::isValidValue($value)) {
            $this->status = $value;
        } else {
            $this->status = ProjectStatus::getDefault();
        }
    }

    public function getStatus(bool $fr = false, bool $raw = false): string
    {
        if (!is_null($this->status)) {
            if ($fr) {
                $return = ProjectStatus::getEnumOptions()[$this->status] ?? 'État inconnu';
            } else {
                $return = $this->status;
            }
            return $raw ? $return : htmlspecialchars($return);
        }
        return ProjectStatus::getDefault();
    }

    // ----- RESOURCE -----
    public function setResource(?float $value): void
    {
        $this->resource = $value;
    }
    public function getResource($raw = false): float
    {
        if (!is_null($this->resource)) {
            return $raw ? $this->resource : htmlspecialchars($this->resource);
        }
        return 0.00;
    }

    // ----- CLIENT -----
    public function setClient(?int $value): void
    {
        $this->client = $value;
    }

    public function getClient($raw = false): int
    {
        if (!is_null($this->client)) {
            return $raw ? $this->client : htmlspecialchars($this->client);
        }
        return 0;
    }

    // ----- DEV -----
    public function setDev(?int $value): void
    {
        $this->dev = $value;
    }
    public function getDev($raw = false): int
    {
        if (!is_null($this->dev)) {
            return $raw ? $this->dev : htmlspecialchars($this->dev);
        }
        return 0;
    }
}
