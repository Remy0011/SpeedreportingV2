<?php

namespace Src\Core;

class EnvLoader
{
    /**
     * Charge les variables d'environnement à partir d'un fichier .env.
     * Les variables sont ajoutées à $_ENV et à l'environnement du système.
     * 
     * @return void
     */
    public static function load(?string $path = null): void
    {
        if ($path === null) {
            $devPath = __DIR__ . '/../../.env.dev';
            $prodPath = __DIR__ . '/../../.env';
            $path = file_exists($devPath) ? $devPath : $prodPath;
        }

        if (!file_exists($path)) {
            throw new \RuntimeException("Le fichier d'environnement n'existe pas: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if (!array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
