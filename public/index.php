<?php

use Src\Controller\ErrorController;
use Src\Controller\LogController;
use Src\Controller\SettingsController;
use Src\Core\ErrorKernel;
use Src\Controller\AuthController;
use Src\Controller\ClientController;
use Src\Controller\DashboardController;
use Src\Controller\ProjectController;
use Src\Controller\UserController;
use Src\Controller\UserPreferencesController;
use Src\Controller\WorkController;
use Src\Core\EnvLoader;
use Src\Services\ProfilePictureService;
use Src\Services\RouterService;

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/../src/Utils/functions.php';

// Charge les variables d'environnement
EnvLoader::load();
if (!getenv('APP_ENV')) {
    die('Les variables d\'environnement ne sont pas définies. Veuillez créer un fichier .env.');
}

// Désactive les erreurs PHP et active le gestionnaire d'erreurs personnalisé
if (getenv('APP_ENV') === 'dev') {
    // DEBUG
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // PRODUCTION
    error_reporting(0);
    ini_set('display_errors', '0');
    ErrorKernel::register();
}

const USER = 2;
const ADMIN = 1;

/* --------------------------------------------------
 * ROUTES DÉFINIES
 * -------------------------------------------------- */

$router = new RouterService();

// DEBUG
$router->addRoute('/pattern', 'GET', function () {
    $pp = new ProfilePictureService();
    $lastKey = array_key_last(ProfilePictureService::$preset_patterns);
    $pp->setPattern(ProfilePictureService::$preset_patterns[$lastKey]);

    $pp->setRowColor('blue');
    $pp->setBackgroundColor('beige');

    foreach (ProfilePictureService::$preset_patterns as $key => $pattern) {
        echo '<h3>' . $key . '</h3>';
        $pp->setPattern($pattern);
        echo '<img src="' . $pp->getProfilePicture() . '" alt="Profile Picture" /></br>';
    }
});

// LOGIN
$router->addRoute('/', 'GET', function () {
    (new AuthController())->getLogin();
});
$router->addRoute('/', 'POST', function () {
    (new AuthController())->postLogin();
});
$router->addRoute('/index.php', 'GET', function () {
    (new AuthController())->getLogin();
});
$router->addRoute('/index.php', 'POST', function () {
    (new AuthController())->postLogin();
});
$router->addRoute('/connexion', 'GET', function () {
    (new AuthController())->getLogin();
});
$router->addRoute('/connexion', 'POST', function () {
    (new AuthController())->postLogin();
});

// LOGOUT
$router->addRoute('/deconnexion', 'GET', function () {
    (new AuthController())->postLogout();
});

// MOT DE PASSE OUBLIÉ
$router->addRoute('/mdp-oublie', 'GET', function () {
    (new AuthController())->getPassword();
});
$router->addRoute('/mdp-oublie', 'POST', function () {
    (new AuthController())->postPassword();
});

// RESET MOT DE PASSE
$router->addRoute('/reset-mdp', 'GET', function () {
    (new AuthController())->getReset();
});
$router->addRoute('/reset-mdp', 'POST', function () {
    (new AuthController())->postReset();
});

// DASHBOARD
$router->addRoute('/dashboard', 'GET', function () {
    (new DashboardController())->getIndex();
}, [USER, ADMIN]);

// HEURES
$router->addRoute('/mes-heures', 'GET', function () {
    (new WorkController())->getSelf();
}, [USER]);
$router->addRoute('/mes-heures', 'POST', function () {
    (new WorkController())->postSelf();
}, [USER]);
$router->addRoute('/update/mes-heures', 'POST', function () {
    (new WorkController())->updateSelf();
}, [USER]);
$router->addRoute('/delete/mes-heures', 'POST', function () {
    (new WorkController())->deleteSelf();
}, [USER]);
$router->addRoute('/valider/mes-heures', 'POST', function () {
    (new WorkController())->validateSelf();
}, [USER]);

$router->addRoute('/heures', 'GET', function () {
    (new WorkController())->getIndex();
}, [ADMIN]);
$router->addRoute('/delete/heures', 'POST', function () {
    (new WorkController())->deleteWork();
}, [ADMIN]);
$router->addRoute('/update/heures', 'POST', function () {
    (new WorkController())->updateWork();
}, [ADMIN]);
$router->addRoute('/heures/valider', 'GET', function () {
    (new WorkController())->getValidate();
}, [ADMIN]);
$router->addRoute('/heures/valider', 'POST', function () {
    (new WorkController())->postValidate();
}, [ADMIN]);

// UTILISATEURS (ADMIN)
$router->addRoute('/utilisateurs', 'GET', function () {
    (new UserController())->getIndex();
}, [ADMIN]);
$router->addRoute('/utilisateurs', 'POST', function () {
    (new UserController())->postUser();
}, [ADMIN]);
$router->addRoute('/update/utilisateurs', 'POST', function () {
    (new UserController())->updateUser();
}, [ADMIN]);
$router->addRoute('/delete/utilisateurs', 'POST', function () {
    (new UserController())->deleteUser();
}, [ADMIN]);

// PROJETS (ADMIN)
$router->addRoute('/projets', 'GET', function () {
    (new ProjectController())->getIndex();
}, [ADMIN]);
$router->addRoute('/projets', 'POST', function () {
    (new ProjectController())->postProject();
}, [ADMIN]);
$router->addRoute('/update/projets', 'POST', function () {
    (new ProjectController())->updateProject();
}, [ADMIN]);
$router->addRoute('/delete/projets', 'POST', function () {
    (new ProjectController())->deleteProject();
}, [ADMIN]);

// CLIENTS (ADMIN)
$router->addRoute('/clients', 'GET', function () {
    (new ClientController())->getIndex();
}, [ADMIN]);
$router->addRoute('/clients', 'POST', function () {
    (new ClientController())->postClient();
}, [ADMIN]);
$router->addRoute('/update/clients', 'POST', function () {
    (new ClientController())->updateClient();
}, [ADMIN]);
$router->addRoute('/delete/clients', 'POST', function () {
    (new ClientController())->deleteClient();
}, [ADMIN]);

// PREFERENCES (USER, ADMIN)
$router->addRoute('/preferences', 'POST', function () {
    (new UserPreferencesController())->save();
}, [USER, ADMIN]);
$router->addRoute('/preferences', 'GET', function () {
    (new UserPreferencesController())->get();
}, [USER, ADMIN]);

if (getenv('APP_ENV') && getenv('APP_ENV') === 'dev') {
    // ERROR
    $router->addRoute('/erreur/{code}', 'GET', function ($params) {
        (new ErrorController())->handleError($params['code']);
    });
}


$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);
