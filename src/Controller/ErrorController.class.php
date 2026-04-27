<?php

namespace Src\Controller;

class ErrorController
{
    private const HTTP_ERRORS = [
        '400' => 'La syntaxe de la requête est erronée.',
        '401' => 'Vous devez vous connecter pour accéder à cette ressource.',
        '403' => 'Vous n\'avez pas l\'autorisation d\'accéder à cette ressource.',
        '404' => 'La ressource demandée est introuvable.',
        '405' => 'La méthode HTTP utilisée n\'est pas autorisée pour cette ressource.',
        '408' => 'Le serveur a expiré en attendant la requête du client.',
        '429' => 'Trop de requêtes envoyées dans un temps donné.',
        '500' => 'Une erreur interne est survenue sur le serveur.',
        '501' => 'Le serveur ne prend pas en charge la fonctionnalité demandée.',
        '502' => 'Le serveur a reçu une réponse invalide d\'un autre serveur.',
        '503' => 'Le serveur est temporairement indisponible, veuillez réessayer plus tard.',
        '504' => 'Le serveur a expiré en attendant la réponse d\'un autre serveur.',
        '511' => 'Le client doit s\'authentifier pour accéder au réseau.',
        '418' => 'Je suis une théière.',
    ];
    
    /**
     * Gère les erreurs HTTP.
     * Cette méthode affiche une page d'erreur HTML
     * en fonction du code d'erreur HTTP fourni.
     * @param int|null $errorCode Le code d'erreur HTTP (par défaut 500).
     * @param string|null $errorMessage Un message d'erreur personnalisé (par défaut null).
     * @return void
     */
    public function handleError($errorCode, $errorMessage = null)
    {
        $errorCode = $errorCode ?? 500;
        $errorMessage ??= $this::HTTP_ERRORS[(string)$errorCode] ?? 'Une erreur est survenue.';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/../src/Views/error.html.php';
    }
}