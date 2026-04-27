<?php

namespace Src\Models\Enums\Type;

use Src\Models\Enums\BaseEnum;

class TokenType extends BaseEnum
{
    public const NON_VALIDE = 'non_valide';
    public const PASSWORD_RESET = 'password_reset';
    public const EMAIL_CONFIRMATION = 'email_confirmation';
    public const AUTH = 'auth';

    public static function getEnumOptions(): array
    {
        return [
            self::PASSWORD_RESET => 'Mot de passe oublié',
            self::EMAIL_CONFIRMATION => 'Confirmation d\'email',
            self::AUTH => 'Authentification'
        ];
    }
}