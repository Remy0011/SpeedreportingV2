<?php

namespace Src\Services;

/** 
 * Service de gestion des tokens de réinitialisation de mot de passe.
 * Ce service permet de générer des tokens aléatoires et de vérifier leur validité.
 * Ces tokens seront stockés en base de données.
 * 
*/
class TokenService
{
    /**
     * Génère un token de réinitialisation de mot de passe avec une date d'expiration.
     *
     * @param int $ttl Durée de validité du token en secondes (par défaut 1 heure)
     * @return string Le token encodé (base64)
     */
    public static function generateToken(int $ttl = 3600): string
    {
        $expiration = time() + $ttl;
        $randomBytes = random_bytes(32);
        $payload = [
            'exp' => $expiration,
            'rnd' => base64_encode($randomBytes)
        ];
        return base64_encode(json_encode($payload));
    }

    /**
     * Récupère la date d'expiration du token.
     *
     * @param string $token Le token encodé (base64)
     * @return int|null Timestamp d'expiration ou null si invalide
     */
    private static function getExpirationFromToken(string $token): ?int
    {
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }
        $payload = json_decode($decoded, true);
        if (!is_array($payload) || !isset($payload['exp'])) {
            return null;
        }
        return (int)$payload['exp'];
    }

    /**
     * Vérifie si le token est expiré.
     * @param string $token Le token encodé (base64)
     * 
     * @return bool True si le token est expiré, false sinon
     */
    public static function isTokenExpired(string $token): bool
    {
        $expiration = self::getExpirationFromToken($token);
        if ($expiration === null) {
            return false; // Token invalide
        }
        return $expiration < time(); // Retourne true si le token est expiré
    }
}
