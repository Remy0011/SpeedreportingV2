<?php

namespace Src\Controller;

use Src\Managers\BaseManager;
use Src\Services\ViewService;

class BaseController extends ViewService
{
    /**
     * Méthode de pagination et récupération des données.
     *
     * @param mixed $manager Instance d'un manager (ex: WorkManager, ProjectManager)
     * @param int $entries_per_page Nombre d'éléments par page
     * @param array|null $criteria Critères de filtrage (facultatif)
     * @return array [
     *   'current_page' => int,
     *   'total_pages' => int,
     *   'total_entries' => int,
     *   'offset' => int,
     * ]
     */
    protected function paginate(mixed $manager, int $entries_per_page = 10, ?array $criteria = null, ?string $function = null): array
    {
        $current_page = isset($_POST['page']) ? (int) $_POST['page'] : (int) ($_GET['page'] ?? 1);

        if ($function !== null && method_exists($manager, $function)) {
            $total_entries = $manager->$function($criteria);
        } else {
            $total_entries = $manager->count($criteria);
        }

        $total_pages = max(1, ceil($total_entries / $entries_per_page));
        $current_page = max(1, min($current_page, $total_pages));
        $offset = ($current_page - 1) * $entries_per_page;

        return [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_entries' => $total_entries,
            'offset' => $offset,
        ];
    }
}
