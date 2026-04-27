<?php

namespace Src\Models\Enums\Type;

use Src\Models\Enums\BaseEnum;

class ClientType extends BaseEnum
{
    public const FINANCE = 'Finance';
    public const INDUSTRIE = 'Industrie';
    public const EDUCATION = 'Éducation';
    public const SANTE = 'Santé';
    public const AGRICULTURE = 'Agriculture';
    public const BTP = 'BTP';
    public const ENERGIE = 'Énergie';
    public const TRANSPORT = 'Transport';
    public const COMMERCE = 'Commerce';
    public const IMMOBILIER = 'Immobilier';
    public const TOURISME = 'Tourisme';
    public const ALIMENTAIRE = 'Agroalimentaire';
    public const ADMINISTRATION_PUBLIQUE = 'Administration publique';
    public const COLLECTIVITE_TERRITORIALE = 'Collectivité territoriale';
    public const ORGANISME_INTERNATIONAL = 'Organisme international';
    public const DEFENSE = 'Défense';
    public const TECHNOLOGIE = 'Technologie';
    public const TELECOMMUNICATIONS = 'Télécommunications';
    public const MEDIAS = 'Médias';
    public const RECHERCHE = 'Recherche & Développement';
    public const ASSOCIATION = 'Association';
    public const ONG = 'ONG';
    public const PARTICULIER = 'Particulier';
    public const FREELANCE = 'Freelance';
    public const FRANCHISE = 'Franchise';
    public const STARTUP = 'Startup';
    public const GRAND_COMPTE = 'Grand compte';
    public const PME = 'PME';
    public const ETI = 'ETI';

    public static function getEnumOptions(): array
    {
        return [
            self::FINANCE,
            self::INDUSTRIE,
            self::EDUCATION,
            self::SANTE,
            self::AGRICULTURE,
            self::BTP,
            self::ENERGIE,
            self::TRANSPORT,
            self::COMMERCE,
            self::IMMOBILIER,
            self::TOURISME,
            self::ALIMENTAIRE,
            self::ADMINISTRATION_PUBLIQUE,
            self::COLLECTIVITE_TERRITORIALE,
            self::ORGANISME_INTERNATIONAL,
            self::DEFENSE,
            self::TECHNOLOGIE,
            self::TELECOMMUNICATIONS,
            self::MEDIAS,
            self::RECHERCHE,
            self::ASSOCIATION,
            self::ONG,
            self::PARTICULIER,
            self::FREELANCE,
            self::FRANCHISE,
            self::STARTUP,
            self::GRAND_COMPTE,
            self::PME,
            self::ETI,
        ];
    }
    
    public static function getGroupedEnumOptions(): array
    {
        return [
            'Secteur privé' => [
                self::FINANCE,
                self::INDUSTRIE,
                self::TECHNOLOGIE,
                self::COMMERCE,
                self::IMMOBILIER,
                self::TRANSPORT,
                self::TOURISME,
                self::ALIMENTAIRE,
                self::ENERGIE,
                self::TELECOMMUNICATIONS,
                self::STARTUP,
                self::GRAND_COMPTE,
                self::PME,
                self::ETI,
                self::FRANCHISE,
            ],
            'Secteur public' => [
                self::EDUCATION,
                self::SANTE,
                self::ADMINISTRATION_PUBLIQUE,
                self::COLLECTIVITE_TERRITORIALE,
                self::DEFENSE,
                self::ORGANISME_INTERNATIONAL,
            ],
            'Tiers-lieux & société civile' => [
                self::ASSOCIATION,
                self::ONG,
            ],
            'Indépendants & particuliers' => [
                self::PARTICULIER,
                self::FREELANCE,
            ],
            'Recherche & médias' => [
                self::RECHERCHE,
                self::MEDIAS,
            ],
            'Autres' => [
                self::AGRICULTURE,
                self::BTP,
            ],
        ];
    }
}
