<?php

namespace Src\Managers;

/**
 * Classe pour gérer les rôles dans la base de données.
 * Elle hérite de la classe de base BaseManager.
 */
class RoleManager extends BaseManager
{
    protected static ?string $table = 'role';
    
}