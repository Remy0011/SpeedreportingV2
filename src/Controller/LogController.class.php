<?php

namespace Src\Controller;

use Src\Managers\LogManager;
use Src\Models\Log;

class LogController extends BaseController
{
    /**
     * Affiche la liste des logs.
     * Cette méthode gère la pagination des logs
     * et récupère les données nécessaires pour l'affichage du tableau des logs.
     *
     * @return void
     */
    public function getIndex()
    {
        // --------------- Données du tableau ---------------
        // Pagination
        $pagination = $this->paginate(manager: new LogManager());
        extract($pagination);

        // Données du tableau
        $rows_raw = (new LogManager())->fetchAll(
            limit: 10,
            offset: $offset
        );

        $data = [];
        foreach ($rows_raw as $row) {
            $data[] = [
                'log' => new Log($row),
            ];
        }

        $table_header = [
            'N°',
            'Date',
            'Résumé',
            'Détails',
            'Utilisateur',
        ];

        $table_data = [];
        foreach ($data as $row) {
            $table_data[$row['log']->getId()] = [
                $row['log']->getId(),
                $row['log']->getDate(),
                $row['log']->getAction(),
                $row['log']->getDetail(),
                $row['log']->getUser(),
            ];
        }

        $detail_data = [];
        foreach ($data as $row) {
            $detail_data[$row['log']->getId()] = [
                'Id' => $row['log']->getId()
            ];
        }

        $edit_data = [];
        foreach ($data as $row) {
            $edit_data[$row['log']->getId()] = [];
        }

        $this::renderAjax('partials/tables/_base', [
            'table_header' => $table_header,
            'table_data' => $table_data,
            'detail_data' => $detail_data,
            'edit_data' => $edit_data,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_entries' => $total_entries,
        ]);

        // Charger une vue
        $this::render('Log/index', [
            'table_header' => $table_header,
            'table_data' => $table_data,
            'detail_data' => $detail_data,
            'edit_data' => $edit_data,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_entries' => $total_entries,
        ]);
    }

    /**
     * Affiche le détail d'un log.
     * Cette méthode récupère l'ID du log depuis les paramètres de la requête
     * et affiche les détails du log correspondant.
     * @param int $log_id L'ID du log à afficher.
     *
     * @return void
     */
    public function getDetail($log_id)
    {
        // Charger une vue
        $this::render('Log/detail', [
            'log_id' => $log_id,
        ]);
    }

    /**
     * Gère la soumission du formulaire de détail d'un log.
     * Cette méthode est appelée lorsque l'utilisateur soumet le formulaire
     * pour afficher les détails d'un log spécifique.
     * @param int $log_id L'ID du log à afficher.
     *
     * @return void
     */
    public function postDetail($log_id) {}
}
