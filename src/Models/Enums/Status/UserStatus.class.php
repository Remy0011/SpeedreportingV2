<?php

namespace Src\Models\Enums\Status;

use Src\Models\Enums\BaseEnum;

class UserStatus extends BaseEnum
{
    public const CONFIRME = 'confirme';
    public const INACTIF = 'inactif';

    public static function getEnumOptions(): array
    {
        return [
            self::CONFIRME => 'Confirmé',
            self::INACTIF => 'Inactif'
        ];
    }

    public static function getDefault(): string
    {
        return self::CONFIRME;
    }

    public static function getColor(string $status): string
    {
        return match ($status) {
            self::CONFIRME => 'success',
            self::INACTIF => 'offline',
            default => 'offline'
        };
    }
}
