<?php

namespace Src\Services;

/**
 * Classe pour gérer la protection CSRF (Cross-Site Request Forgery).
 * Elle génère un token CSRF sécurisé et le compare avec celui envoyé dans les requêtes POST.
 */
class CsrfService
{
    private static $tokenKey = '_csrf_token';

    /**
     * Génère un token CSRF sécurisé et le stocke dans la session.
     * 
     * @return string Le token CSRF généré.
     */
    public static function generateToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Génère un token sécurisé si aucun n’existe
        if (empty($_SESSION[self::$tokenKey])) {
            $_SESSION[self::$tokenKey] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::$tokenKey];
    }

    /**
     * Echo un champ caché (hidden) contenant le token CSRF dans un formulaire HTML.
     * 
     * @return void
     */
    public static function insertToken(): void
    {
        $token = self::generateToken();
        echo '<input type="hidden" name="' . self::$tokenKey . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Vérifie si le token CSRF envoyé dans la requête POST est valide.
     * Le compare avec celui stocké dans la session.
     * 
     * @return bool true si le token est valide, false sinon.
     */
    public static function isValid(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_POST[self::$tokenKey], $_SESSION[self::$tokenKey])) {
            return false;
        }

        return hash_equals($_SESSION[self::$tokenKey], $_POST[self::$tokenKey]);
    }
}
?>
