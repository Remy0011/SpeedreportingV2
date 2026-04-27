<?php

namespace Src\Controller;

use Src\Services\AuthService;
use Src\Managers\UserPreferencesManager;

class UserPreferencesController extends BaseController
{
    /**
     * Enregistre les préférences de l'utilisateur.
     * Cette méthode vérifie si l'utilisateur est authentifié,
     * vérifie les données reçues,
     * et enregistre les préférences dans la base de données.
     *
     * @return void
     */
    public function save()
    {
        header('Content-Type: application/json');

        if (!AuthService::isAuthenticated()) {
            http_response_code(403);
            echo json_encode(['error' => 'Non autorisé']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['key'], $data['value'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }

        $userId = AuthService::getUser()->getId();
        $manager = new UserPreferencesManager();

        $success = $manager->savePreference($userId, $data['key'], $data['value'] ? '1' : '0');

        echo json_encode(['success' => $success]);
    }

    /**
     * Récupère les préférences de l'utilisateur.
     * Cette méthode vérifie si l'utilisateur est authentifié,
     * et renvoie les préférences stockées dans la base de données.
     *
     * @return void
     */
    public function get()
    {
        header('Content-Type: application/json');

        if (!AuthService::isAuthenticated()) {
            http_response_code(403);
            echo json_encode(['error' => 'Non autorisé']);
            return;
        }

        $userId = AuthService::getUser()->getId();
        $manager = new UserPreferencesManager();

        $preferences = $manager->getPreferencesByUser($userId);

        echo json_encode($preferences);
    }
}
