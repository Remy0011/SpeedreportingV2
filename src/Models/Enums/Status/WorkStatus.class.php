<?php

namespace Src\Models\Enums\Status;

use Src\Models\Enums\BaseEnum;

class WorkStatus extends BaseEnum
{
    public const EN_ATTENTE = 'en_attente';
    public const CONFIRME = 'confirme';
    public const INACTIF = 'inactif';
    public const EN_COURS_DE_CREATION = 'en_cours_de_creation';

    public static function getEnumOptions(): array
    {
        return [
            self::EN_ATTENTE => 'En attente',
            self::CONFIRME => 'Confirmé',
            self::INACTIF => 'Inactif',
            self::EN_COURS_DE_CREATION => 'En cours de création',
        ];
    }

    public static function getDefault(): string
    {
        return self::EN_ATTENTE;
    }

    public static function getColor(string $status): string
    {
        return match ($status) {
            self::EN_ATTENTE => 'danger',
            self::CONFIRME => 'success',
            self::INACTIF => 'offline',
            self::EN_COURS_DE_CREATION => 'progress',
            default => 'offline',
        };
    }

    public static function getLabel(string $status): string
    {
        return self::getEnumOptions()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}
