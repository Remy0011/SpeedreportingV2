<?php

namespace Src\Services;

class AssetService
{
    // Liste des fichiers CSS et JS à charger
    private static $styles = [];
    private static $scripts = [];

    /* ---------- GETTER & SETTER ---------- */

    // ----- STYLES -----
    /**
     * Ajouter un fichier CSS à la liste des styles à charger
     * 
     * @param string|array $file Le nom du fichier CSS ou un tableau de noms de fichiers CSS
     * @return void
     */
    public static function addStyle(string|array $file)
    {
        if (is_array($file)) {
            foreach ($file as $f) {
                self::addStyle($f);
            }
            return;
        }
        if (!in_array($file, self::$styles)) {
            self::$styles[] = $file;
        }
    }

    public static function getStyles()
    {
        return self::$styles;
    }

    // ----- SCRIPTS -----
    /**
     * Ajouter un fichier JS à la liste des scripts à charger
     * 
     * @param string|array $file Le nom du fichier JS ou un tableau de noms de fichiers JS
     * @return void
     */
    public static function addScript(string|array $file)
    {
        if (is_array($file)) {
            foreach ($file as $f) {
                self::addScript($f);
            }
            return;
        }
        if (!in_array($file, self::$scripts)) {
            self::$scripts[] = $file;
        }
    }

    public static function getScripts()
    {
        return self::$scripts;
    }
}
