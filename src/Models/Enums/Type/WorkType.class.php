<?php

namespace Src\Models\Enums\Type;

use Src\Models\Enums\BaseEnum;

class WorkType extends BaseEnum
{
    public const TRAVAIL = 'travail';
    public const CONGES = 'conges';
    public const MALADIE = 'maladie';
    public const ABSENCE = 'absence';

    public static function getEnumOptions(): array
    {
        return [
            self::TRAVAIL => 'Travail',
            self::CONGES => 'Congés',
            self::MALADIE => 'Maladie',
            self::ABSENCE => 'Absence',
        ];
    }

    public static function getDefault(): string
    {
        return self::TRAVAIL;
    }
}
