<?php

namespace Src\Controller;

use DateTimeImmutable;
use Src\Core\ErrorKernel;
use Src\Managers\ProjectManager;
use Src\Managers\UserManager;
use Src\Managers\WorkManager;
use Src\Models\Enums\Status\WorkStatus;
use Src\Models\Project;
use Src\Models\User;
use Src\Models\Work;
use Src\Services\CsrfService;


class PlanningController extends BaseController
{
    public function getIndex(): void
    {
        $month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year  = isset($_GET['year'])  && is_numeric($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');
        $view  = $_GET['view'] ?? 'projects';

        if ($month < 1 || $month > 12) $month = (int) date('n');
        if ($year < 2020 || $year > 2120) $year = (int) date('Y');

        $calendarData = $this->generateCalendar($month, $year);

        // Charger les projets en cours
        $projectsRaw = (new ProjectManager())->getTableData(filters: ['status' => 'en_cours']);
        $projects = [];
        foreach ($projectsRaw as $row) {
            $projects[] = new Project($row);
        }

        // Charger tous les collaborateurs (hors utilisateur système id 0)
        $usersRaw = (new UserManager())->getTableData(limit: 1000);
        $users = [];
        foreach ($usersRaw as $row) {
            $users[(int) $row['user_id']] = new User($row);
        }

        // Planning data : créneaux du mois, regroupés par date puis par utilisateur
        $planning_data = [];
        $workRows = (new WorkManager())->getMonthWork((string) $month, (string) $year);
        foreach ($workRows as $row) {
            $work = new Work($row);
            if (!$work->getYear(true) || !$work->getWeek(true) || !$work->getDay(true)) {
                continue;
            }
            $d = new DateTimeImmutable();
            $d = $d->setISODate($work->getYear(true), $work->getWeek(true), $work->getDay(true));
            $date = $d->format('Y-m-d');
            $planning_data[$date][$work->getUser()] = [
                'work' => $work,
                'project_name' => $row['project_name'] ?? '',
                'user_firstname' => $row['user_firstname'] ?? '',
                'user_lastname' => $row['user_lastname'] ?? '',
            ];
        }

        $this::render('Planning/index', array_merge($calendarData, [
            'projects'     => $projects,
            'users'        => $users,
            'planning_data' => $planning_data,
            'view'         => $view,
        ]));
    }

    /**
     * Gère la création, modification et suppression des créneaux de travail
     * du planning prévisionnel (vue administrateur).
     *
     * Champs requis :
     * - command : 'create', 'update' ou 'delete'
     *
     * @return void
     */
    public function postPlanning(): void
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        $command = $_POST['command'] ?? null;
        $commands = ['create', 'update', 'delete'];

        if (!in_array($command, $commands)) {
            ErrorKernel::throwHttpError(400, "Command non valide.");
        }

        $workManager = new WorkManager();

        switch ($command) {
            case 'create':
                $required_fields = ['work_user', 'work_project', 'work_count', 'work_date'];
                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || $_POST[$field] === '') {
                        ErrorKernel::throwHttpError(400, "Le champ $field est requis.");
                    }
                }

                $work_date = new DateTimeImmutable($_POST['work_date']);

                $work = new Work($_POST);
                $work->setUser((int) $_POST['work_user']);
                $work->setProject((int) $_POST['work_project']);
                $work->setCount((float) $_POST['work_count']);
                $work->setStatus(WorkStatus::CONFIRME);
                $work->setYear((int) $work_date->format('o'));
                $work->setWeek((int) $work_date->format('W'));
                $work->setDay((int) $work_date->format('N'));

                $workManager->save($work);
                break;

            case 'update':
                if (empty($_POST['work_id'])) {
                    ErrorKernel::throwHttpError(400, "Le champ work_id est requis.");
                }

                $work_raw = $workManager->find((int) $_POST['work_id']);
                if (!$work_raw) {
                    ErrorKernel::throwHttpError(404, "Créneau non trouvé.");
                }

                $work = new Work($work_raw);

                if (isset($_POST['work_count']) && $_POST['work_count'] !== '') {
                    $work->setCount((float) $_POST['work_count']);
                }
                if (isset($_POST['work_description'])) {
                    $work->setDescription($_POST['work_description']);
                }

                $workManager->update($work);
                break;

            case 'delete':
                if (empty($_POST['work_id'])) {
                    ErrorKernel::throwHttpError(400, "Le champ work_id est requis.");
                }

                $workManager->delete((int) $_POST['work_id']);
                break;
        }

        $month = isset($_POST['current_month']) && is_numeric($_POST['current_month']) ? (int) $_POST['current_month'] : (int) date('n');
        $year  = isset($_POST['current_year'])  && is_numeric($_POST['current_year'])  ? (int) $_POST['current_year']  : (int) date('Y');

        if ($month < 1 || $month > 12) $month = (int) date('n');
        if ($year < 2020 || $year > 2120) $year = (int) date('Y');

        $calendarData = $this->generateCalendar($month, $year);

        $projectsRaw = (new ProjectManager())->getTableData(filters: ['status' => 'en_cours']);
        $projects = [];
        foreach ($projectsRaw as $row) {
            $projects[] = new Project($row);
        }

        $usersRaw = (new UserManager())->getTableData(limit: 1000);
        $users = [];
        foreach ($usersRaw as $row) {
            $users[(int) $row['user_id']] = new User($row);
        }

        $planning_data = [];
        $workRows = $workManager->getMonthWork((string) $month, (string) $year);
        foreach ($workRows as $row) {
            $w = new Work($row);
            if (!$w->getYear(true) || !$w->getWeek(true) || !$w->getDay(true)) {
                continue;
            }
            $d = new DateTimeImmutable();
            $d = $d->setISODate($w->getYear(true), $w->getWeek(true), $w->getDay(true));
            $date = $d->format('Y-m-d');
            $planning_data[$date][$w->getUser()] = [
                'work' => $w,
                'project_name' => $row['project_name'] ?? '',
                'user_firstname' => $row['user_firstname'] ?? '',
                'user_lastname' => $row['user_lastname'] ?? '',
            ];
        }

        $this::renderAjax(
            'Planning/week_calendar',
            array_merge($calendarData, [
                'projects'      => $projects,
                'users'         => $users,
                'planning_data' => $planning_data,
                'view'          => 'calendar',
            ])
        );
    }

    private function generateCalendar(int $month, int $year): array
    {
        $current  = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $previous = $current->modify('-1 month');
        $next     = $current->modify('+1 month');
        $today    = new DateTimeImmutable('today');

        $months = [
            'current' => [
                'year'  => (int) $current->format('Y'),
                'month' => (int) $current->format('n'),
                'name'  => getFrenchMonthName($current->format('n')),
            ],
            'previous' => [
                'year'  => (int) $previous->format('Y'),
                'month' => (int) $previous->format('n'),
            ],
            'next' => [
                'year'  => (int) $next->format('Y'),
                'month' => (int) $next->format('n'),
            ],
            'today' => [
                'year'  => (int) $today->format('Y'),
                'month' => (int) $today->format('n'),
            ],
        ];

        $firstDay = $current;
        $lastDay  = $current->modify('last day of this month');
        $start    = $firstDay->modify('monday this week');
        $end      = $lastDay->modify('sunday this week');
        $weeks    = [];

        for ($weekStart = $start; $weekStart <= $end; $weekStart = $weekStart->modify('+1 week')) {
            $weekEnd = $weekStart->modify('+6 days');
            $week = [
                'number' => (int) $weekStart->format('W'),
                'year'   => (int) $weekStart->format('o'),
                'range'  => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'days'   => [],
            ];

            for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                $date      = $weekStart->modify("+{$dayOffset} days");
                $dayNumber = (int) $date->format('N');

                $week['days'][] = [
                    'name'             => getFrenchDayName($dayNumber),
                    'number'           => (int) $date->format('d'),
                    'date'             => $date->format('Y-m-d'),
                    'display'          => $date->format('d/m'),
                    'is_current_month' => (int) $date->format('n') === $month,
                    'is_today'         => $date->format('Y-m-d') === $today->format('Y-m-d'),
                ];
            }

            $weeks[] = $week;
        }

        return [
            'months' => $months,
            'weeks'  => $weeks,
        ];
    }
}