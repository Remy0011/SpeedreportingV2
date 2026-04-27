<?php

namespace Src\Core;

use Src\Controller\ErrorController;

/**
 * Classe ErrorKernel
 * 
 * Cette classe gère les erreurs et exceptions globales de l'application.
 * Elle enregistre des gestionnaires pour les exceptions, les erreurs PHP
 * et les arrêts inattendus du script.
 */
class ErrorKernel
{

    /**
     * Démarre les gestionnaires d'erreurs et d'exceptions.
     * 
     * @return void
     */
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Gère les exceptions non capturées.
     * 
     * @param \Throwable $exception L'exception à gérer.
     * @return void
     */
    public static function handleException($exception): void
    {
        $code = method_exists($exception, 'getCode') && $exception->getCode() ? $exception->getCode() : 500;
        (new ErrorController())->handleError($code, $exception->getMessage());
        exit;
    }

    /**
     * Gère les erreurs HTTP.
     * 
     * @param int $code Le code d'erreur HTTP.
     * @param string $message Le message d'erreur.
     */
    public static function throwHttpError(int $code, string $message): never
    {
        throw new \RuntimeException($message, $code);
    }

    /**
     * Gère les erreurs PHP.
     * 
     * @param int $errno Le numéro d'erreur.
     * @param string $errstr Le message d'erreur.
     * @param string $errfile Le fichier où l'erreur s'est produite.
     * @param int $errline La ligne où l'erreur s'est produite.
     * @return bool
     */
    public static function handleError($errno, $errstr, $errfile, $errline): bool
    {
        throw new \ErrorException($errstr, 500, $errno, $errfile, $errline);
    }

    /**
     * Gère les arrêts inattendus du script.
     * 
     * @return void
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
            (new ErrorController())->handleError(500, $error['message']);
        }
    }
}
