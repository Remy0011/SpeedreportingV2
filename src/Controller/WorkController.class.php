<?php

namespace Src\Controller;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use Src\Core\ErrorKernel;
use Src\Managers\ProjectManager;
use Src\Managers\RoleManager;
use Src\Managers\UserManager;
use Src\Managers\WorkManager;
use Src\Models\Client;
use Src\Models\Enums\Status\WorkStatus;
use Src\Models\Project;
use Src\Models\Role;
use Src\Models\User;
use Src\Models\Week;
use Src\Models\Work;
use Src\Services\AuthService;
use Src\Services\CsrfService;

class WorkController extends BaseController
{

    /**
     * Liste des heures de travail
     * Possibilité de passer un paramètre dans l'url pour choisir la page à afficher
     * 
     * ?page=int
     * 
     * @return void
     */
    public function getIndex(): void
    {
        // Récupération des filtres depuis $_GET
        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'client_name' => $_GET['client_name'] ?? null,
            'project_id' => $_GET['project_id'] ?? null,
            'week' => $_GET['week'] ?? null,
            'year' => $_GET['year'] ?? null,
        ];

        // Pagination
        $pages = $this->paginate(new WorkManager(), function: 'countTableData', criteria: $filters);

        // Données
        $rows_raw = (new WorkManager())->getTableData(page: $pages['current_page'], limit: 10, filters: $filters);

        $data = [];
        foreach ($rows_raw as $row) {
            $data[] = [
                'work' => new Work($row),
                'user' => new User($row),
                'project' => new Project($row),
                'client' => new Client($row),
            ];
        }

        $projects = (new ProjectManager())->getOptions();
        $work_to_validate = (new WorkManager())->countToValidate() > 0;

        $groupedData = [];

        foreach ($data as $entry) {
            $userId = $entry['user']->getId();
            $week = $entry['work']->getWeek();
            $key = $userId . '_' . $week;
            $projectId = $entry['project']->getId();

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'user' => $entry['user'],
                    'week' => $week,
                    'works' => [],
                    'works_by_project' => [],
                ];
            }

            $groupedData[$key]['works'][] = $entry['work'];

            if (!isset($groupedData[$key]['works_by_project'][$projectId])) {
                $groupedData[$key]['works_by_project'][$projectId] = [
                    'project' => $entry['project'],
                    'client' => $entry['client'],
                    'entries' => [],
                ];
            }

            $groupedData[$key]['works_by_project'][$projectId]['entries'][] = $entry['work'];
        }

        $this::renderAjax('partials/tables/_work', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'user_id' => $filters['user_id'],
            'client_name' => $filters['client_name'],
            'week' => $filters['week'],
            'year' => $filters['year'],
            'data' => $data,
            'groupedData' => $groupedData,
            'pages' => $pages,
            'projects' => $projects,
        ]);

        $this::render('Work/index', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'user_id' => $filters['user_id'],
            'client_name' => $filters['client_name'],
            'week' => $filters['week'],
            'year' => $filters['year'],
            'data' => $data,
            'pages' => $pages,
            'groupedData' => $groupedData,
            'projects' => $projects,
            'work_to_validate' => $work_to_validate,
        ]);
    }

    /**
     * Supprime une entrée de travail.
     * Cette méthode vérifie le token CSRF pour éviter les attaques CSRF,
     * et s'assure que la requête est une requête AJAX.
     * Elle récupère l'ID du travail à supprimer depuis les données POST,
     * vérifie que le travail existe, et le supprime de la base de données.
     * 
     * @return void
     */
    public function deleteWork()
    {
        $work_id = $_POST['work_id'] ?? null;
        if (!$work_id) {
            ErrorKernel::throwHttpError(400, "L'ID du travail est requis.");
        }

        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $work_raw = (new WorkManager())->find($work_id);
        if (!$work_raw) {
            ErrorKernel::throwHttpError(404, "Travail non trouvé.");
        }

        $work = new Work($work_raw);

        (new WorkManager())->delete($work->getId());

        $this->getIndex();
    }

    public function updateWork()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $work_id = $_POST['work_id'] ?? null;
        if (!$work_id) {
            ErrorKernel::throwHttpError(400, "L'ID du travail est requis.");
        }

        $work_raw = (new WorkManager())->find($work_id);
        if (!$work_raw) {
            ErrorKernel::throwHttpError(404, "Travail non trouvé.");
        }

        $work = new Work($work_raw);

        $work->setCount($_POST['work_count'] ?? null);
        $work->setDescription($_POST['work_description'] ?? null);
        $work->setStatus($_POST['work_status'] ?? null);

        (new WorkManager())->save($work);

        $this->getIndex();
    }

    /**
     * Liste des heures de travail qui concernent l'utilisateur connecté
     * @return void
     */
    public function getSelf(): void
    {
        $user = AuthService::getUser();
        if (!$user) {
            ErrorKernel::throwHttpError(404, "Utilisateur non trouvé.");
        }

        $month_current = (isset($_GET['month']) && is_numeric($_GET['month'])) ? (int) $_GET['month'] : null;
        $year_current = (isset($_GET['year']) && is_numeric($_GET['year'])) ? (int) $_GET['year'] : null;

        extract($this->generateCalendar($user, $month_current, $year_current));

        $hours_left = 35.0 - (float) (new WorkManager())->getUserHoursToValidateCount($user->getId());
        $can_validate = (new WorkManager())->canValidateUser($user->getId());

        $this::renderAjax(
            'partials/calendar/_calendar',
            [
                'months' => $months,
                'weeks' => $weeks,
                'weeks_data' => $weeks_data,
                'hours_left' => $hours_left,
                'can_validate' => $can_validate,
            ]
        );

        // Rendu complet
        $this::render('Work/self', [
            'user' => $user,
            'project_options' => (new ProjectManager())->getOptions(),
            'months' => $months,
            'weeks' => $weeks,
            'weeks_data' => $weeks_data,
            'hours_left' => $hours_left,
            'can_validate' => $can_validate,
        ]);
    }

    /**
     * Gère le traitement de l'entrée de l'utilisateur connecté
     * Champ requis :
     * - command : 'create' ou 'confirm'
     * 
     * Si l'command est 'create', les champs suivants sont requis :
     * - work_count : nombre d'heures
     * - work_project : projet
     * 
     * Si l'command est 'confirm', aucun champ n'est requis.
     * Tous les travaux en cours de création seront validés.
     * 
     * @return void
     */
    public function postSelf()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        $user = AuthService::getUser();
        if (!$user) {
            ErrorKernel::throwHttpError(404, "Utilisateur non trouvé.");
        }

        $commands = ['create', 'confirm'];
        $command = $_POST['command'] ?? null;

        if (!in_array($command, $commands)) {
            ErrorKernel::throwHttpError(400, "Command non valide.");
        }

        switch ($command) {
            case 'create':
                $required_fields = [
                    'work_count',
                    'work_project',
                ];
                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        ErrorKernel::throwHttpError(400, "Le champ $field est requis.");
                    }
                }
                $this->createWork($user);
                $response = [
                    'success' => true,
                    'command' => 'create',
                    'message' => 'Travail créé avec succès.'
                ];
                break;
            case 'confirm':
                (new WorkManager())->setUserWorkWaiting($user->getId());
                $response = [
                    'success' => true,
                    'command' => 'confirm',
                    'message' => 'Travaux validés avec succès.'
                ];
                break;
            default:
                ErrorKernel::throwHttpError(400, "Command non valide.");
        }

        $hours_left = 35.0 - (float)(new WorkManager())->getUserHoursToValidateCount($user->getId());
        $can_validate = (new WorkManager())->canValidateUser($user->getId());

        extract($this->generateCalendar($user));

        $this::renderAjax(
            'partials/calendar/_full',
            [
                'user' => $user,
                'project_options' => (new ProjectManager())->getOptions(),
                'months' => $months,
                'weeks' => $weeks,
                'weeks_data' => $weeks_data,
                'hours_left' => $hours_left,
                'can_validate' => $can_validate,
            ]
        );

        // Rendu complet
        $this::render('Work/self', [
            'user' => $user,
            'project_options' => (new ProjectManager())->getOptions(),
            'months' => $months,
            'weeks' => $weeks,
            'weeks_data' => $weeks_data,
            'hours_left' => $hours_left,
            'can_validate' => $can_validate,
        ]);
    }

    public function validateSelf()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if ($this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $user = AuthService::getUser();
        if (!$user) {
            ErrorKernel::throwHttpError(404, "Utilisateur non trouvé.");
        }

        (new WorkManager())->validateSelfUser($user->getId());

        redirect('/mes-heures');
    }

    /**
     * Supprime une entrée de travail de l'utilisateur connecté.
     * 
     * @return void
     */
    public function deleteSelf()
    {
        $work_id = $_POST['work_id'] ?? null;
        if (!$work_id) {
            ErrorKernel::throwHttpError(400, "L'ID du travail est requis.");
        }

        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $user = AuthService::getUser();
        if (!$user) {
            ErrorKernel::throwHttpError(404, "Utilisateur non trouvé.");
        }

        $work_raw = (new WorkManager())->find($work_id);
        if (!$work_raw) {
            ErrorKernel::throwHttpError(404, "Travail non trouvé.");
        }

        $work = new Work($work_raw);

        if ($work->getUser() !== $user->getId()) {
            ErrorKernel::throwHttpError(403, "Vous ne pouvez pas supprimer ce travail.");
        }

        (new WorkManager())->delete($work->getId());

        $hours_left = 35.0 - (float)(new WorkManager())->getUserHoursToValidateCount($user->getId());
        $can_validate = (new WorkManager())->canValidateUser($user->getId());

        extract($this->generateCalendar($user, $_POST['current_month'], $_POST['current_year']));

        $this::renderAjax(
            'partials/calendar/_full',
            [
                'user' => $user,
                'project_options' => (new ProjectManager())->getOptions(),
                'months' => $months,
                'weeks' => $weeks,
                'weeks_data' => $weeks_data,
                'hours_left' => $hours_left,
                'can_validate' => $can_validate,
            ]
        );
    }

    /**
     * Crée une entrée de travail pour l'utilisateur connecté.
     * 
     * @param User $user L'utilisateur connecté.
     * @return void
     */
    private function createWork($user)
    {
        $work_date_str = $_POST['work_date'] ?? null;
        if ($work_date_str) {
            $work_date = new DateTime($work_date_str);
            $work_year = (int) $work_date->format('Y');
            $work_week = (int) $work_date->format('W');
            $work_day = (int) $work_date->format('N');
        } else {
            $work_date = new DateTime();
            $work_year = (int) $work_date->format('Y');
            $work_week = (int) $work_date->format('W');
            $work_day = null;
        }

        $work = new Work($_POST);

        $work->setUser($user->getId());
        $work->setStatus(WorkStatus::EN_COURS_DE_CREATION);
        $work->setYear($work_year);
        $work->setWeek($work_week);
        $work->setDay($work_day);
        $work->setCreation((new DateTime())->format('Y-m-d H:i:s'));

        (new WorkManager())->save($work);
    }

    /**
     * Génère le calendrier pour l'utilisateur.
     * 
     * Cette méthode construit les données nécessaires à l'affichage d'un calendrier mensuel
     * pour un utilisateur donné, en tenant compte du mois et de l'année courants (ou de ceux passés en paramètre).
     * Elle retourne un tableau contenant :
     * - les informations sur le mois courant, précédent, suivant et aujourd'hui,
     * - la liste des semaines du mois courant,
     * - les données de travail de l'utilisateur regroupées par semaine.
     *
     * @param User $user L'utilisateur pour lequel générer le calendrier.
     * @param int|null $month_current Le mois à afficher (1-12), ou null pour le mois courant.
     * @param int|null $year_current L'année à afficher, ou null pour l'année courante.
     * @return array Un tableau associatif contenant les données du calendrier.
     */
    private function generateCalendar(User $user, ?int $month_current = null, ?int $year_current = null): array
    {
        // Calculer le mois courant, précédent et suivant à partir de 'current'
        $months = [
            'current' => [
                'year' => $year_current ?? (int) date('Y'),
                'month' => $month_current ?? (int) date('m'),
            ],
        ];
        $months['current']['name'] = getFrenchMonthName($months['current']['month']);

        // Calcul du timestamp du premier jour du mois courant
        $currentTimestamp = mktime(0, 0, 0, $months['current']['month'], 1, $months['current']['year']);

        // Mois précédent
        $prevTimestamp = strtotime('-1 month', $currentTimestamp);
        $months['previous'] = [
            'year' => (int) date('Y', $prevTimestamp),
            'month' => (int) date('m', $prevTimestamp),
            'name' => getFrenchMonthName((int) date('m', $prevTimestamp)),
        ];

        // Mois suivant
        $nextTimestamp = strtotime('+1 month', $currentTimestamp);
        $months['next'] = [
            'year' => (int) date('Y', $nextTimestamp),
            'month' => (int) date('m', $nextTimestamp),
            'name' => getFrenchMonthName((int) date('m', $nextTimestamp)),
        ];

        // Aujourd'hui
        $months['today'] = [
            'year' => (int) date('Y'),
            'month' => (int) date('m'),
            'name' => getFrenchMonthName((int) date('m')),
        ];

        // Générer un tableau des semaines du mois courant
        $weeks = [];
        $year = $months['current']['year'];
        $month = $months['current']['month'];

        // Premier et dernier jour du mois
        $firstDay = new DateTimeImmutable("$year-$month-01");
        $lastDay = $firstDay->modify('last day of this month');

        // Trouver le lundi de la première semaine du mois
        $start = $firstDay->modify('monday this week');
        // Trouver le dimanche de la dernière semaine du mois
        $end = $lastDay->modify('sunday this week');

        $period = new DatePeriod($start, new DateInterval('P1W'), $end->modify('+1 day'));

        foreach ($period as $weekStart) {
            $weekEnd = $weekStart->modify('+6 days');
            $weekNumber = (int) $weekStart->format('W');
            $weeks[$weekNumber] = $weekStart->format('d/m') . ' <i class=\'bx bx-arrow-from-left\'></i> ' . $weekEnd->format('d/m');
        }

        $weeks_data_raw = (new WorkManager())->getMonthUserWork(
            userId: $user->getId(),
            month: $months['current']['month'],
            year: $months['current']['year']
        );

        $weeks_data = array_fill_keys(array_keys($weeks), []);

        foreach ($weeks_data_raw as $row) {
            $work = new Work($row);
            $project = new Project($row);
            $weeks_data[$work->getWeek()][$work->getId()] = [
                'work' => $work,
                'project' => $project
            ];
        }

        return [
            'months' => $months,
            'weeks' => $weeks,
            'weeks_data' => $weeks_data,
        ];
    }

    /**
     * Modifie une entrée de travail de l'utilisateur connecté.
     * Seules les champs autorisés peuvent être modifiés.
     * 
     * @param int $id L'ID de l'entrée de travail à supprimer.
     * @return void
     */
    public function updateSelf()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $user = AuthService::getUser();
        if (!$user) {
            ErrorKernel::throwHttpError(404, "Utilisateur non trouvé.");
        }

        $work_id = $_POST['work_id'] ?? null;
        if (!$work_id) {
            ErrorKernel::throwHttpError(400, "L'ID du travail est requis.");
        }

        $work_raw = (new WorkManager())->find($work_id);
        if (!$work_raw) {
            ErrorKernel::throwHttpError(404, "Travail non trouvé.");
        }

        $work = new Work($work_raw);

        if ($work->getUser() !== $user->getId()) {
            ErrorKernel::throwHttpError(403, "Vous ne pouvez pas modifier ce travail.");
        }

        $work->setCount($_POST['work_count'] ?? null);
        $work->setDescription($_POST['work_description'] ?? null);

        (new WorkManager())->save($work);

        $hours_left = 35.0 - (float)(new WorkManager())->getUserHoursToValidateCount($user->getId());
        $can_validate = (new WorkManager())->canValidateUser($user->getId());

        extract($this->generateCalendar($user, $_POST['current_month'] ?? null, $_POST['current_year'] ?? null));

        $this::renderAjax(
            'partials/calendar/_full',
            [
                'user' => $user,
                'project_options' => (new ProjectManager())->getOptions(),
                'months' => $months,
                'weeks' => $weeks,
                'weeks_data' => $weeks_data,
                'hours_left' => $hours_left,
                'can_validate' => $can_validate,
            ]
        );
    }

    public function getValidate()
    {
        // --------------- Données du tableau ---------------
        $filters = [
            'search' => $_GET['search'] ?? null,
            'project_id' => $_GET['project_id'] ?? null,
            'year' => $_GET['year'] ?? null,
            'week' => $_GET['week'] ?? null,
        ];

        $pages = $this->paginate(manager: new WorkManager(), function: 'countToValidate', criteria: $filters);

        $rows_raw = (new WorkManager())->getToValidate(
            $pages['current_page'],
            10,
            filters: $filters
        );

        $data = [];
        foreach ($rows_raw as $row) {
            $work = new Work($row);
            $user = new User($row);
            $key = $work->getYear() . '_' . $work->getWeek() . '_' . $user->getId();
            if (!isset($data[$key])) {
                $data[$key] = [
                    'week' => new Week(['year' => $work->getYear(), 'week' => $work->getWeek(), 'user' => $user]),
                ];
            }
            $data[$key]['week']->addData(
                [
                    'work' => $work,
                    'user' => $user,
                    'project' => new Project($row),
                ]
            );
        }

        $this::renderAjax(
            'partials/tables/_validate',
            [
                'search' => $filters['search'],
                'data' => $data,
                'pages' => $pages,
            ]
        );

        $projects = (new ProjectManager())->getOptions();

        $this::render('Work/validate', [
            'search' => $filters['search'],
            'data' => $data,
            'pages' => $pages,
            'projects' => $projects,
            'year' => $filters['year'],
            'week' => $filters['week'],
            'project_id' => $filters['project_id'],
        ]);
    }

    public function postValidate()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        if ($this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        $work_ids = $_POST['work_id'] ?? null;
        if (!$work_ids || !is_array($work_ids)) {
            ErrorKernel::throwHttpError(400, "Les ID des travaux sont requis.");
        }

        $status = $_POST['work_status'] ?? WorkStatus::CONFIRME;
        if (!$status || !in_array($status, WorkStatus::getValues())) {
            ErrorKernel::throwHttpError(400, "Le statut du travail doit être valide.");
        }
        (new WorkManager())->changeWorkStatus($work_ids, $status);

        $this->getValidate();
    }
}
