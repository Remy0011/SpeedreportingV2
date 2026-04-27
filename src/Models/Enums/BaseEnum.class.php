<?php

namespace Src\Models\Enums;

use ReflectionClass;

/** 
 * Classe abstraite de base pour les énumérations
 * Fournit des méthodes utilitaires pour gérer les énumérations MySQL.
 * Cette classe doit être étendue par des sous-classes représentant des énumérations spécifiques.
 * Les sous-classes doivent implémenter la méthode abstraite getStatusOptions() pour fournir
 * une liste des options disponibles dans l'énumération.
 * 
 * @package Src\Models\Enums
 */

abstract class BaseEnum
{
    public const NON_VALIDE = 'non_valide';
    /**
     * Renvoie un équivalent en français de la valeur de l'énumération.
     * Utilisé pour afficher la valeur dans une interface utilisateur.
     * Seule les valeurs que l'administrateur peut choisir doivent être retournées.
     *
     * @return array Liste des équivalent en français des valeurs de l'énumération.
     * @note Cette méthode est abstraite et doit être implémentée dans les sous-classes.
     */
    abstract public static function getEnumOptions(): array;

    /**
     * Fonction pour vérifier si une valeur est valide pour l'énumération.
     * Utilise la méthode getValues() pour obtenir la liste des valeurs valides.
     * 
     * @param mixed $value La valeur à vérifier.
     * @return bool Vrai si la valeur est valide, faux sinon.
     */
    public static function isValidValue($value): bool
    {
        return in_array($value, static::getValues());
    }

    /**
     * Fonction pour obtenir la liste des valeurs de l'énumération.
     * Utilise la réflexion pour obtenir les constantes de la classe.
     * 
     * @return array Liste des valeurs de l'énumération.
     */
    public static function getValues(): array
    {
        return (new ReflectionClass(static::class))->getConstants();
    }

    /**
     * Fonction pour obtenir la valeur par défaut de l'énumération.
     * Utilise la méthode getValues() pour obtenir la première valeur de la liste.
     * Si aucune constante n'est définie, retourne une chaîne vide.
     * 
     * @return string La valeur par défaut de l'énumération ou une chaîne vide si aucune valeur n'est définie.
     */
    public static function getDefault(): string
    {
        return self::NON_VALIDE;
    }
}
