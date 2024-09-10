<?php

namespace App\Http\Responses;

class RolesPermisoResponse
{
    public int $Id;
    public int $RolId;
    public string $Pantalla;
    public bool $Ver;
    public Array $roles_detalles;

    public function __construct()
    {
        return "construct function was initialized.";
    }
}
