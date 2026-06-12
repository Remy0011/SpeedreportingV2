<?php

namespace Src\Models\Enums\Status;

use Src\Models\Enums\BaseEnum;

class ProjectStatus extends BaseEnum
{
    public const EN_ATTENTE = 'en_attente';
    public const EN_COURS = 'en_cours';
    public const TERMINE = 'termine';
    public const SUSPENDU = 'suspendu';

    public static function getEnumOptions(): array
    {
        return [
            self::EN_ATTENTE => 'En attente',
            self::EN_COURS => 'En cours',
            self::TERMINE => 'Terminé',
            self::SUSPENDU => 'Suspendu',
        ];
    }

    public static function getDefault(): string
    {
        return self::EN_ATTENTE;
    }

    public static function getColor(string $status): string
    {
        return match ($status) {
            self::EN_ATTENTE => 'offline',
            self::EN_COURS => 'progress',
            self::TERMINE => 'success',
            self::SUSPENDU => 'danger',
            default => 'offline',
        };
    }
}
