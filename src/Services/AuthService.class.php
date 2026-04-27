<?php

namespace Src\Services;

use Src\Managers\UserManager;
use Src\Models\Role;
use Src\Managers\RoleManager;
use Src\Models\User;

/**
 * Classe qui gère l'authentification des utilisateurs
 */
class AuthService
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Connecté l'utilisateur avec les identifiants fournis
     * 
     * @param string $email
     * @param string $password
     * @return bool true si l'utilisateur est connecté, false sinon
     */
    public function login(string $email, string $password): bool
    {
        $user_manager = new UserManager();

        if ($user_row = $user_manager->authenticate($email, $password)) {
            $user = new User($user_row);
            $_SESSION['user'] = [
                'id' => $user->getId(),
                'role' => $user->getRole(),
            ];
            return true;
        }

        return false;
    }

    /**
     * Deconnecte l'utilisateur connecté
     * 
     * @return void
     */
    public function logout(): void
    {
        $_SESSION = [];
        session_unset();
        session_destroy();
    }

    /**
     * Retourne l'utilisateur connecté
     * 
     * @return User|null L'objet User de l'utilisateur connecté ou null si l'utilisateur n'est pas connecté
     */
    public static function getUser(): ?User
    {
        if (!isset($_SESSION['user']['id'])) {
            return null;
        }
     
        $user_manager = new UserManager();
        $user_row = $user_manager->find($_SESSION['user']['id'], ['user_id', 'user_email','user_firstname','user_lastname', 'user_picture','user_role']);
        return new User($user_row);
    }
    
    /**
     * Retourne le rôle de l'utilisateur connecté
     * 
     * @return Role|null L'objet Role de l'utilisateur connecté ou null si l'utilisateur n'est pas connecté
     */
    public static function getRole(): ?Role
    {
        if (!isset($_SESSION['user']['role'])) {
            return null;
        }
        
        $role_manager = new RoleManager();
        $role_row = $role_manager->find($_SESSION['user']['role']);

        return new Role($role_row);
    }

    /**
     * Vérifie si l'utilisateur est connecté
     * 
     * @return bool true si l'utilisateur est connecté, false sinon
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user']['id']);
    }

    public static function isAdmin(): bool
    {
        $role = self::getRole();
        return $role && $role->getName() === 'admin';
    }
}
