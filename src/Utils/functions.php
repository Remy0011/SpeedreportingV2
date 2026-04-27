<?php

// Load vendor autoload if available
require_once __DIR__ . '/../../vendor/autoload.php';

// Définir le fuseau horaire par défaut
date_default_timezone_set('Europe/Paris');

// Définir le jour de début de semaine (lundi)
setlocale(LC_TIME, 'fr_FR.UTF-8');

// Définition du chemin vers le dossier "src", un niveau au-dessus de la racine du document
$_SRC = $_SERVER["DOCUMENT_ROOT"] . "/../src/";

// Enregistre la fonction d'autoload personnalisée
spl_autoload_register("loadClass");

/**
 * Fonction d'autoload : elle permet de charger automatiquement les classes quand elles sont utilisées.
 * Elle supprime le préfixe de namespace "Src\" et remplace les backslashes (\) par des slashes (/)
 * pour retrouver le fichier correspondant à la classe dans le dossier "src/".
 */
function loadClass($className)
{
    // Supprime le namespace "Src\" du nom de la classe
    $className = str_replace("Src\\", "", $className);
    
    // Remplace les backslashes (\) par des slashes (/) pour construire un chemin de fichier
    $className = str_replace("\\", "/", $className);
    
    // Construit le chemin complet du fichier de la classe (suffixé par ".class.php")
    $file = __DIR__ . "/../../src/" . $className . ".class.php";

    // Inclut le fichier s'il existe
    if (file_exists($file)) {
        require_once $file;
    }
}

/**
 * Fonction de redirection HTTP vers un chemin donné.
 * Elle accepte en option un code de réponse HTTP (ex : 301, 302).
 *
 * @param string $path Le chemin vers lequel rediriger
 * @param int|null $response_code Code HTTP facultatif (défaut : 302)
 * 
 * @throws InvalidArgumentException si le chemin est vide ou si le code HTTP est invalide
 */
function redirect(string $path, ?int $response_code = null): void
{
    if (empty($path)) {
        throw new InvalidArgumentException("Le chemin de redirection ne peut pas être vide.");
    }

    if ($response_code != null && ($response_code < 100 || $response_code > 599)) {
        throw new InvalidArgumentException("Le code de réponse HTTP fourni n'est pas valide : $response_code.");
    }

    if ($response_code != null) {
        http_response_code($response_code);
    }

    // Redirection HTTP
    header("Location: " . $path, true, $response_code ?? 302); // 302 par défaut
    exit(); // Interrompt le script après la redirection
}

/**
 * Supprime un fichier s’il existe.
 * Fonction unlink safe.
 *
 * @param string $fullpath Chemin complet vers le fichier à supprimer
 */
function deleteFile($fullpath)
{
    if (file_exists($fullpath)) {
        unlink($fullpath); // Supprime le fichier
    }
}

/**
 * Fonction pour échapper les caractères spéciaux d’une chaîne pour affichage HTML sécurisé.
 * Utilise htmlspecialchars().
 *
 * @param string $string Chaîne à échapper
 * @return string Chaîne sécurisée pour affichage HTML
 */
function hsc($string)
{
    if (empty($string)) {
        return ""; // Retourne une chaîne vide si la valeur est vide
    } else {
        return htmlspecialchars($string); // Échappe les caractères spéciaux HTML
    }
}

/**
 * Génère une route avec des restrictions basées sur les rôles autorisés.
 *
 * @param mixed $uri L'URI de la route.
 * @param mixed $method La méthode HTTP associée à la route (GET, POST, etc.).
 * @param mixed $allowedRoles Les rôles autorisés à accéder à cette route.
 * @param mixed $callback La fonction de rappel à exécuter lorsque la route est appelée.
 * @return void
 */ 
function routeWithRole($uri, $method, $allowedRoles, $callback)
{
    global $requestUri, $requestMethod;

    if ($requestUri === $uri && $requestMethod === $method) {
        $user = $_SESSION['user'] ?? null;
        if (!$user || !in_array($user['role'], $allowedRoles)) {
            http_response_code(403);
            echo "Accès refusé";
            exit;
        }
        $callback();
        exit;
    }
}

/**
 * Fait un var_dump et termine le script.
 * 
 * @param mixed $var
 * @return never
 */
function var_dump_exit($var){
    var_dump($var);
    exit;
}

function shortenDescription(string $description, int $maxLength = 50): string {
    if (strlen($description) > $maxLength) {
        return substr($description, 0, $maxLength) . '...';
    }
    return $description;
}

/**
 * Retourne le nom du mois en français pour un numéro de mois donné.
 *
 * @param int|string $month Le numéro du mois (1-12)
 * @return string Le nom du mois en français (ex: "janvier")
 */
function getFrenchMonthName($month)
{
    // $month doit être entre 1 et 12
    $timestamp = mktime(0, 0, 0, $month, 1);
    $months = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];
    return $months[(int)$month] ?? date('F', $timestamp);
}

/**
 * Retourne le nom du jour de la semaine en français pour un numéro de jour donné.
 *
 * @param int $day Le numéro du jour (1-7, où 1 = Lundi, 2 = Mardi, etc.)
 * @return string Le nom du jour en français (ex: "Lundi")
 */
function getFrenchDayName($day)
{
    $days = [
        1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi',
        5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
    ];
    return $days[$day] ?? '';
}

/**
 * Affiche un message dans la console du navigateur.
 * Utilise json_encode pour échapper correctement les caractères spéciaux.
 * @param mixed $message Le message à afficher dans la console
 */
function consoleLog($message)
{
    echo "<script>console.log(" . json_encode($message) . ");</script>";
}

/** * Interpole deux couleurs RGB en fonction d'un facteur t (0 à 1).
 * 
 * @param array $color1 La première couleur RGB (ex: [255, 0, 0])
 * @param array $color2 La deuxième couleur RGB (ex: [0, 0, 255])
 * @param float $t Le facteur d'interpolation (0 = color1, 1 = color2)
 * @return array La couleur interpolée RGB
 */
function lerpColor($color1, $color2, $t) {
    return [
        round($color1[0] + ($color2[0] - $color1[0]) * $t),
        round($color1[1] + ($color2[1] - $color1[1]) * $t),
        round($color1[2] + ($color2[2] - $color1[2]) * $t)
    ];
}

?>
