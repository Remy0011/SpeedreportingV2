<?php

namespace Src\Controller;

use Src\Core\ErrorKernel;
use Src\Managers\UserManager;
use Src\Models\User;
use Src\Services\AuthService;
use Src\Services\CsrfService;
use Src\Services\MailService;
use Src\Services\TokenService;
use Src\Services\ViewService;

class AuthController extends ViewService
{
    /**
     * Affiche le formulaire de connexion si l'utilisateur n'est pas connecté.
     * Redirige vers le tableau de bord si l'utilisateur est déjà authentifié.
     *
     * @return void
     */
    public function getLogin()
    {
        if (AuthService::isAuthenticated()) {
            redirect("/dashboard");
            exit;
        }

        $error = isset($_GET['error']) ? hsc(urldecode($_GET['error'])) : null;
        $message = isset($_GET['message']) ? hsc(urldecode($_GET['message'])) : null;

        $this::render('Auth/login', [
            'error' => $error,
            'message' => $message,
        ]);
    }

    /**
     * Cette méthode gère la soumission du formulaire de connexion,
     * vérifie les identifiants de l'utilisateur et redirige en fonction du résultat.
     *
     * @return never
     */
    public function postLogin()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, "Une erreur est survenue, merci de bien vouloir réessayer.");
        }

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($email && $password) {
            $auth_service = new AuthService();

            if ($auth_service->login($email, $password)) {
                redirect("/dashboard");
                exit;
            } else {
                $error = "Nom d'utilisateur ou mot de passe incorrect.";
                redirect("/connexion?error=" . urlencode($error));
                exit;
            }
        } else {
            $error = "Veuillez remplir tous les champs.";
            redirect("/connexion?error=" . urlencode($error));
            exit;
        }
    }

    /**
     * Déconnect l'utilisateur et le redirige vers la page de connexion.
     *
     * @return never
     */
    public function postLogout()
    {
        $auth_service = new AuthService();
        $auth_service->logout();
        redirect("/connexion");
        exit;
    }

    /**
     * Affiche le formulaire de demande de réinitialisation du mot de passe.
     *
     * @return void
     */
    public function getPassword()
    {
        $this::render('Auth/password');
    }

    /**
     * Affiche le formulaire de réinitialisation du mot de passe.
     * Vérifie le token et l'email fournis dans l'URL.
     * Si le token est valide et non expiré, affiche le formulaire de réinitialisation.
     * Si le token est invalide ou expiré, redirige vers la page de connexion avec un message d'erreur.
     *
     * @return void
     */
    public function getReset()
    {
        $token = $_GET['token'] ?? null;
        $email = $_GET['email'] ?? null;

        if (!$token || !$email) {
            redirect("/connexion?error=" . urlencode('Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.'));
        }

        // Vérification du token
        $user_manager = new UserManager();
        $user_raw = $user_manager->getUserByEmail($email, ['user_id', 'user_email']);
        if (!$user_raw) {
            ErrorKernel::throwHttpError(404, 'Utilisateur non trouvé.');
        }

        $user = new User($user_raw);
        $token_exist = $user_manager->verifyUserToken($user->getId(), $token);
        if (!$token_exist) {
            redirect("/connexion?error=" . urlencode('Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.'));
        }

        // Vérifie que le token n'a pas expiré
        if (TokenService::isTokenExpired($token)) {
            redirect("/connexion?error=" . urlencode('Le lien de réinitialisation du mot de passe a expiré. Veuillez en demander un nouveau.'));
        }

        // Affiche le formulaire de réinitialisation du mot de passe
        $this::render('Auth/reset', [
            'email' => $user->getEmail(),
            'token' => $token,
            'message' => isset($_GET['message']) ? hsc(urldecode($_GET['message'])) : null,
            'error' => isset($_GET['error']) ? hsc(urldecode($_GET['error'])) : null
        ]);
    }

    /**
     * Gère la soumission du formulaire de réinitialisation du mot de passe.
     * Vérifie le token et l'email fournis dans le formulaire.
     * Si le token est valide et non expiré, change le mot de passe.
     * Si le token est invalide ou expiré, redirige vers la page de connexion avec un message d'erreur.
     *
     * @return void
     */
    public function postReset(){
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, 'Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.');
        }

        $this::verifyRequiredFields([
            'email',
            'password',
            'password_confirm',
            'token'
        ]);

        $email = $_POST['email'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $token = $_POST['token'];

        if ($password !== $password_confirm) {
            redirect("/reset-mdp?email=" . urlencode($email) . "&token=" . urlencode($token) . "&error=" . urlencode('Les mots de passe ne correspondent pas.'));
        }

        // Vérification du token
        $user_manager = new UserManager();
        $user_raw = $user_manager->getUserByEmail($email, ['user_id', 'user_email']);
        if (!$user_raw) {
            redirect("/connexion?error=" . urlencode('Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.'));
        }

        $user = new User($user_raw);
        $token_exist = $user_manager->verifyUserToken($user->getId(), $token);
        if (!$token_exist) {
            redirect("/connexion?error=" . urlencode('Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.'));
        }

        // Vérifie que le token n'a pas expiré
        if (TokenService::isTokenExpired($token)) {
            redirect("/connexion?error=" . urlencode('Le lien de réinitialisation du mot de passe a expiré. Veuillez en demander un nouveau.'));
        }

        // Change le mot de passe
        if ($user_manager->changePassword($user->getId(), $password)) {
            // Supprime le token de réinitialisation du mot de passe
            $user_manager->deleteUserToken($user->getId(), $token);
            redirect("/connexion?message=" . urlencode('Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.'));
        } else {
            redirect("/connexion?error=" . urlencode('Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.'));
        }
    }

    /**
     * Gère la soumission du formulaire de demande de réinitialisation du mot de passe.
     * 
     * @return void
     */
    public function postPassword()
    {
        if (!CsrfService::isValid()) {
            ErrorKernel::throwHttpError(403, 'Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.');
        }

        $this::verifyRequiredFields([
            'email'
        ]);

        $email = $_POST['email'];

        $user_raw = (new UserManager())->getUserByEmail($email, ['user_id', 'user_email']);
        if (!$user_raw) {
            // Par mesure de sécurité, on ne dit pas si l'utilisateur existe ou non.
            redirect("/connexion?error=" . urlencode('Une erreur est survenue lors de la réinitialisation du mot de passe, merci de bien vouloir réessayer.'));
        } 
        
        $user = new User($user_raw);
        $token = TokenService::generateToken();
        (new UserManager())->storeUserToken($user->getId(), $token);

        if (MailService::sendPasswordReset($user->getEmail(), $token)) {
            redirect("/connexion?message=" . urlencode('Un e-mail de réinitialisation du mot de passe a été envoyé à l\'adresse fournie.'));
        } else {
            redirect("/connexion?error=" . urlencode('Une erreur est survenue lors de l\'envoi de l\'e-mail de réinitialisation du mot de passe, merci de bien vouloir réessayer.'));
        }
    }
}
