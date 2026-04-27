<?php

namespace Src\Controller;

use Src\Core\ErrorKernel;
use Src\Managers\RoleManager;
use Src\Managers\UserManager;
use Src\Models\Enums\Status\UserStatus;
use Src\Models\Role;
use Src\Models\Token;
use Src\Models\User;
use Src\Services\AuthService;
use Src\Services\CsrfService;
use Src\Services\MailService;
use Src\Services\ProfilePictureService;
use Src\Services\TokenService;

class UserController extends BaseController
{
    /**
     * Affiche la liste des utilisateurs.
     * Cette méthode gère les filtres de recherche, de statut et de rôle,
     * ainsi que la pagination des résultats.
     * Elle récupère les données des utilisateurs et des rôles
     * depuis les gestionnaires appropriés et les affiche dans la vue principale.
     * 
     * @return void
     */
    public function getIndex()
    {
        // Filtres
        $filters = [
            'search'   => $_GET['search']   ?? null,
            'status'   => $_GET['status']   ?? null,
            'role_id'  => $_GET['role_id']  ?? null,
        ];

        // Pagination
        $pages = $this->paginate(
            manager: new UserManager(),
            function: 'getTableCount',
            criteria: $filters
        );

        $rows_raw = (new UserManager())->getTableData(
            page: $pages['current_page'],
            limit: 10,
            filters: $filters
        );

        $data = [];
        foreach ($rows_raw as $row) {
            $data[] = [
                'user' => new User($row),
                'role' => new Role($row),
            ];
        }

        $roles_raw = (new RoleManager())->fetchAll();
        $roles = [];
        foreach ($roles_raw as $row) {
            $roles[] = new Role($row);
        }

        // Vue Ajax (si utilisée en dynamique)
        $this::renderAjax('partials/tables/_user', [
            'data' => $data,
            'pages' => $pages,
            'roles' => $roles,
            'search' => $filters['search'],
            'status' => $filters['status'],
            'role_id' => $filters['role_id'],
        ]);

        // Vue principale
        $this::render('User/index', [
            'data' => $data,
            'pages' => $pages,
            'roles' => $roles,
            'search' => $filters['search'],
            'status' => $filters['status'],
            'role_id' => $filters['role_id'],
        ]);
    }

    /**
     * Gère la création d'un nouvel utilisateur
     * 
     * Liste des champs attendu dans le formulaire :
     * 
     * - user_email : text - obligatoire - Adresse email de l'utilisateur
     * - user_lastname : text - obligatoire - Nom de l'utilisateur
     * - user_firstname : text - obligatoire - Prénom de l'utilisateur
     * - user_role : int - défaut à 1 (user) - Rôle de l'utilisateur (1 = user, 2 = admin) (voir la table user_role)
     * 
     * @return void
     */
    public function postUser()
    {
        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        $this::verifyRequiredFields([
            'user_email',
            'user_lastname',
            'user_firstname',
        ]);

        if (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            ErrorKernel::throwHttpError(400, "L'adresse email n'est pas valide.");
        }

        // Vérifier que l'email n'existe pas déjà dans la base de données
        $user_manager = new UserManager();
        if ($user_manager->findBy(['user_email' => $_POST['user_email']], ['user_email'])) {
            ErrorKernel::throwHttpError(400, "L'adresse email existe déjà.");
        }

        // Créer un nouvel utilisateur
        $user = new User($_POST);
        $user->setRole(isset($_POST['user_role']) ? (int)$_POST['user_role'] : 1);
        $profilePictureService = new ProfilePictureService();
        $user->setPicture($profilePictureService->getProfilePicture());
        $user->setStatus(UserStatus::INACTIF);

        $user_manager->save($user);

        $user_id = $user_manager->getUserByEmail($user->getEmail(), ['user_id'])['user_id'] ?? null;
        if (!$user_id) {
            ErrorKernel::throwHttpError(500, "Une erreur est survenue lors de la création de l'utilisateur.");
        }

        // Envoi de l'email de création de compte
        $token = TokenService::generateToken();
        if (!MailService::sendAccountCreated($user->getEmail(), $token, $user->getName())) {
            ErrorKernel::throwHttpError(500, "Une erreur est survenue lors de l'envoi de l'email de création de compte.");
        }
        $user_manager->storeUserToken($user_id, $token);

        $this->getIndex();
    }

    /**
     * Supprime un utilisateur.
     * Cette méthode vérifie le token CSRF pour éviter les attaques CSRF,
     * et s'assure que la requête est une requête AJAX.
     * Elle récupère l'ID de l'utilisateur à supprimer depuis les données POST,
     * vérifie que l'utilisateur existe, et le supprime de la base de données.
     *
     * @return void
     */
    public function deleteUser()
    {
        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        $auth_user = AuthService::getUser();
        $user_id = $_POST['user_id'] ?? null;

        if ($auth_user->getId() === $user_id) {
            ErrorKernel::throwHttpError(403, "Vous ne pouvez pas supprimer votre propre compte.");
        }

        if (!$user_id) {
            ErrorKernel::throwHttpError(400, "L'ID de l'utilisateur est requis.");
        }

        $user_manager = new UserManager();
        $user_raw = $user_manager->find($user_id);
        if (!$user_raw) {
            ErrorKernel::throwHttpError(404, "Utilisateur non trouvé.");
        }

        $user_manager->deleteUser($user_id);

        $this->getIndex();
    }

    /**
     * Met à jour un utilisateur.
     * Cette méthode vérifie le token CSRF pour éviter les attaques CSRF,
     * et s'assure que la requête est une requête AJAX.
     * Elle récupère l'ID de l'utilisateur à mettre à jour depuis les données POST,
     * hydrate l'objet User avec les nouvelles données,
     * et enregistre les modifications dans la base de données.
     *
     * @return void
     */
    public function updateUser()
    {
        if (!$this::requestIsAjax()) {
            ErrorKernel::throwHttpError(403, "Accès interdit.");
        }

        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Token CSRF invalide.");
        }

        $user_id = $_POST['user_id'] ?? null;
        if (!$user_id) {
            ErrorKernel::throwHttpError(400, "L'ID de l'utilisateur est requis.");
        }

        $user_manager = new UserManager();
        $user_raw = $user_manager->find($user_id);
        if (!$user_raw) {
            ErrorKernel::throwHttpError(404, "Utilisateur non trouvé.");
        }

        $user = new User($user_raw);
        $user->hydrate($_POST);

        $user_manager->save($user);

        $this->getIndex();
    }
}
