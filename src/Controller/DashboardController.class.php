<?php

namespace Src\Controller;

use Src\Managers\CardManager;
use Src\Services\AuthService;

class DashboardController extends BaseController
{
    /**
     * Affiche le tableau de bord.
     * Cette méthode récupère les données nécessaires pour l'affichage du tableau de bord
     * - Pour les administrateurs, elle récupère les volumes de travail hebdomadaires,
     * les projets importants, les statistiques de projets par client,
     * - Pour les utilisateurs, elle affiche le volume de travail hebdomadaire de l'utilisateur,
     * 
     * @return void
     */
    public function getIndex()
    {
        $user = AuthService::getUser();
        $userId = $user->getId();

        $data = [];
        if (AuthService::isAdmin()) {
            // Admin dashboard
            $data['weekWork_data'] = (new CardManager())->getWeeklyWorkVolume();
            $data['work_to_validate'] = (new CardManager())->countToValidate() > 0;
            $data['projectStatus_data'] = (new CardManager())->getProjectStatusCounts();
            $data['importantProjects'] = (new CardManager())->getImportantProjects();
            $data['projectsByClientType'] = (new CardManager())->getProjectCountByClientType();
            $data['projectsByClient'] = (new CardManager())->getProjectCountByClient();
            $data['haveBreakUser'] = (new CardManager())->getHaveBreakUser();
        } else {
            // User dashboard
            $in_week_day = date('w');
            $work_to_enter = $in_week_day < 2 || $in_week_day > 4;

            $data['work_to_enter'] = $work_to_enter;

            $progress = 0;
            if ($in_week_day >= 1 && $in_week_day <= 5) {
                $progress = (($in_week_day - 1) / 4) * 100;
            }

            $data['hoursReminder'] = [
                'progress' => round($progress),
                'day' => $in_week_day,
            ];

            $data['userWeekWork'] = (new CardManager())->getUserWeeklyWorkVolume($userId);
            $data['userWorkProject'] = (new CardManager())->getUserProjectHours($userId);
        }

        $this::render('home', [
            'data' => $data,
        ]);
    }
}
