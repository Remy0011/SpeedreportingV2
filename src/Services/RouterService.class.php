<?php

namespace Src\Services;

use Error;
use Src\Controller\ErrorController;
use Src\Core\ErrorKernel;
use Src\Services\AuthService;

class RouterService {
    private $routes = [];

    /**
     * Ajoute une route.
     *
     * @param string   $uri          Chemin de la route avec paramètres optionnels (ex: /heures/utilisateur/{id}).
     * @param string   $method       Méthode HTTP (GET, POST, etc.).
     * @param callable $callback     Fonction à exécuter.
     * @param array|null $allowedRoles (Optionnel) Rôles autorisés.
     */
    public function addRoute(string $uri, string $method, callable $callback, ?array $allowedRoles = null): void {
        // Convertir le chemin en pattern regex :
        // Remplacer les segments {param} par des groupes nommés en regex
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $uri);

        // Encadrer le pattern par des délimiteurs et forcer la correspondance complète.
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'pattern'      => $pattern,
            'uri'          => $uri, // Pour référence, si besoin
            'method'       => strtoupper($method),
            'callback'     => $callback,
            'allowedRoles' => $allowedRoles
        ];
    }

    /**
     * Parcourt les routes et exécute le callback correspondant en passant les paramètres extraits.
     *
     * @param string $requestUri    URI demandée.
     * @param string $requestMethod Méthode HTTP demandée.
     */
    public function dispatch(string $requestUri, string $requestMethod): void {
        $requestMethod = strtoupper($requestMethod);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Vérifier si l'URI correspond au pattern
            if (preg_match($route['pattern'], $requestUri, $matches)) {
                // Récupérer uniquement les paramètres nommés
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Vérifier les rôles si nécessaires
                $allowedRoles = $route['allowedRoles'] ?? null;
                $user = AuthService::getUser();

                if ($allowedRoles !== null) {
                    if (!($user && in_array($user->getRole(), $allowedRoles))) {
                        if (!isset($_SESSION['user'])) {
                            header("Location: /connexion");
                            exit;
                        }
                        $error = "Accès refusé";
                        ErrorKernel::throwHttpError(403, $error);
                    }
                }

                // Exécuter le callback en passant les paramètres
                call_user_func($route['callback'], $params);
                return;
            }
        }

        // Aucune route trouvée
        $error = "Page non trouvée";
        ErrorKernel::throwHttpError(404, $error);
    }
}
