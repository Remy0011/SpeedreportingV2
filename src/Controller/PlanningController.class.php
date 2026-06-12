<?php

namespace Src\Controller;

use DateTimeImmutable;
use Src\Managers\ProjectManager;
use Src\Managers\UserManager;
use Src\Managers\WorkManager;
use Src\Models\Project;


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

        $projectManager = new ProjectManager();

        // Charger les projets en cours
        $projectsRaw = $projectManager->getTableData(filters: ['status' => 'en_cours']);
        $projects = [];
        foreach ($projectsRaw as $row) {
            $projects[] = new Project($row);
        }

        // Planning data (vide pour l'instant)
        $planning_data = [];
        $selectedWeek = $this->getSelectedWeek($calendarData['weeks']);
        $userRows = [];

        if ($view === 'users') {
            $users = (new UserManager())->getPlanningUsers();
            $weeklyLoads = (new WorkManager())->getWeeklyUserLoads($selectedWeek['number'], $selectedWeek['year']);

            foreach ($users as $user) {
                $userId = (int) $user['user_id'];
                $userRows[] = [
                    'id' => $userId,
                    'name' => trim(($user['user_firstname'] ?? '') . ' ' . ($user['user_lastname'] ?? '')),
                    'role' => $user['role_fr'] ?? '',
                    'hours' => $weeklyLoads[$userId] ?? 0.0,
                ];
            }
        }

        $this::render('Planning/index', array_merge($calendarData, [
            'projects'     => $projects,
            'planning_data' => $planning_data,
            'view'         => $view,
            'selected_week' => $selectedWeek,
            'user_rows'     => $userRows,
        ]));
    }

    private function getSelectedWeek(array $weeks): array
    {
        $selectedWeekNumber = isset($_GET['week']) && is_numeric($_GET['week']) ? (int) $_GET['week'] : ($weeks[0]['number'] ?? (int) date('W'));
        $selectedWeekYear = isset($_GET['week_year']) && is_numeric($_GET['week_year']) ? (int) $_GET['week_year'] : null;

        foreach ($weeks as $week) {
            if ($week['number'] === $selectedWeekNumber && ($selectedWeekYear === null || $week['year'] === $selectedWeekYear)) {
                return $week;
            }
        }

        return $weeks[0];
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
