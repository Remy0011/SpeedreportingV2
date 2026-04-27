<?php

namespace Src\Services;

use Error;
use Src\Core\ErrorKernel;

class ViewService
{
    /**
     * Rendu d'une vue HTML.
     * @param string $view Le nom de la vue à rendre (sans extension).
     * @param array $params Les paramètres à passer à la vue.
     * 
     * @throws ErrorKernel Si la vue n'est pas trouvée.
     */
    protected static function render(string $view, array $params = [])
    {
        extract($params);
        
        $partial_view_path = "Views/$view.html.php";

        $view_path = $_SERVER['DOCUMENT_ROOT'] . '/../src/' . $partial_view_path;

        if (file_exists($view_path)) {
            ob_start();
            require $view_path;
            $content = ob_get_clean();
            echo $content;
        } else {
            ErrorKernel::throwHttpError(500, "Vue non trouvée : $partial_view_path");
        }
    }

    /**
     * Rendu d'une vue HTML pour les appels AJAX.
     * Si ce n'est pas un appel AJAX, la vue n'est pas rendue.
     * @param string $view Le nom de la vue à rendre (sans extension).
     * @param array $params Les paramètres à passer à la vue.
     */
    protected static function renderAjax(string $view, array $params = [])
    {
        // Vérifie si c’est un appel AJAX
        if (self::requestIsAjax()) {
            self::render($view, $params);
            exit;
        }
    }

    /**
     * Vérifie si la requête est un appel AJAX.
     * 
     * @return bool True si c'est un appel AJAX, sinon false.
     */
    protected static function requestIsAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'FetchRequest';
    }

    /**
     * Vérifie les champs requis dans $_POST.
     * @param array $required_fields Les champs requis à vérifier.
     * 
     * @return bool True si tous les champs requis sont présents et non vides, sinon false.
     */
    protected static function verifyRequiredFields(array $required_fields): bool
    {
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                ErrorKernel::throwHttpError(400, "Le champ '$field' est requis.");
                return false;
            }
        }
        return true;
    }
}
