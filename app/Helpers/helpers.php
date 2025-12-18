<?php

use App\Helpers\RoleHelper;

if (!function_exists('hasRole')) {
    /**
     * Verificar si el usuario tiene uno o más roles específicos
     *
     * @param string|array $roles
     * @return bool
     */
    function hasRole($roles)
    {
        return RoleHelper::hasRole($roles);
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Verificar si el usuario es Administrador o Director
     *
     * @return bool
     */
    function isAdmin()
    {
        return RoleHelper::isAdmin();
    }
}

if (!function_exists('canManageMesaPartes')) {
    /**
     * Verificar si el usuario puede gestionar Mesa de Partes
     *
     * @return bool
     */
    function canManageMesaPartes()
    {
        return RoleHelper::canManageMesaPartes();
    }
}

if (!function_exists('canPublishNews')) {
    /**
     * Verificar si el usuario puede publicar noticias
     *
     * @return bool
     */
    function canPublishNews()
    {
        return RoleHelper::canPublishNews();
    }
}

if (!function_exists('canManageUsers')) {
    /**
     * Verificar si el usuario puede gestionar usuarios y roles
     *
     * @return bool
     */
    function canManageUsers()
    {
        return RoleHelper::canManageUsers();
    }
}

if (!function_exists('canManageDirective')) {
    /**
     * Verificar si el usuario puede gestionar el comité directivo
     *
     * @return bool
     */
    function canManageDirective()
    {
        return RoleHelper::canManageDirective();
    }
}
