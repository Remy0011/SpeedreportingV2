<?php

namespace Src\Models\Enums\Status;

use Src\Models\Enums\BaseEnum;

class ProjectStatus extends BaseEnum
{
    public const EN_COURS = 'en_cours';
    public const TERMINE = 'termine';
    public const ANNULE = 'annule';

    public static function getEnumOptions(): array
    {
        return [
            self::EN_COURS => 'En cours',
            self::TERMINE => 'Terminé',
            self::ANNULE => 'Annulé',
        ];
    }

    public static function getDefault(): string
    {
        return self::EN_COURS;
    }

    public static function getColor(string $status): string
    {
        return match ($status) {
            self::EN_COURS => 'progress',
            self::TERMINE => 'success',
            self::ANNULE => 'danger',
            default => 'offline',
        };
    }
}
