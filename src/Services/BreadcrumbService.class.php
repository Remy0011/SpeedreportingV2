<?php

namespace Src\Services;

/**
 * Classe qui gère le fil d'Ariane (breadcrumb) de l'application
 */
class BreadcrumbService
{
    /**
     * Retourne les éléments du fil d'Ariane (breadcrumb) en fonction de l'URL actuelle
     * 
     * @return array Un tableau du fil d'Ariane contenant les noms et les URL des segments
     *               de l'URL actuelle. Chaque élément est un tableau associatif avec les clés 'name' et 'url'.
     */
    public static function getBreadcrumbs(): array
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($requestUri, '/'));
        $breadcrumbs = [];

        $url = "";
        $count = count($segments);

        foreach ($segments as $key => $segment) {
            $url .= "/" . $segment;

            $name = is_numeric($segment) ? "ID " . $segment : ucfirst($segment);
            $breadcrumbs[] = [
                'name' => $name,
                'url' => ($key === $count - 1) ? null : $url
            ];
        }

        return $breadcrumbs;
    }
}
