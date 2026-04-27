<?php

use Src\Core\EnvLoader;

require_once __DIR__ . "/../src/Utils/functions.php";

// Variables d'environnement
EnvLoader::load();

// Récupération des variables d'environnement
$host = getenv("DB_HOST");
$port = getenv("DB_PORT");
$dbname = getenv("DB_DATABASE");
$user = getenv("DB_USERNAME");
$pass = getenv("DB_PASSWORD");

try {
    // Connexion à MySQL sans spécifier de base de données
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // utf8mb4 pour régler le problème d'encodage
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");

    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Utiliser la base de données
    $pdo->exec("USE `$dbname`");

    // Récupération et exécution du fichier data.sql pour créer les tables
    $dbSqlFile = __DIR__ . "/data.sql";
    if (!file_exists($dbSqlFile)) {
        throw new Exception("Le fichier db.sql n'existe pas : $dbSqlFile");
    }
    $dbSql = file_get_contents($dbSqlFile);
    if ($dbSql === false) {
        throw new Exception("Impossible de lire le fichier db.sql : $dbSqlFile");
    }
    $pdo->exec($dbSql);

    $testData = __DIR__ . "/test_data.sql";
    if (file_exists($testData)) {
        $testDataSql = file_get_contents($testData);
        if ($testDataSql === false) {
            throw new Exception("Impossible de lire le fichier test_data.sql : $testData");
        }
        $pdo->exec($testDataSql);
    }

    echo "Base de données réinitialisée et données injectées avec succès.";
} catch (PDOException $e) {
    echo "Erreur lors de la connexion à la base de données : " . $e->getMessage();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
