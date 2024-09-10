<?php

namespace App\Http\Responses;

class RolesDetallesResponse
{
    public int $Id;
    public int $RolPermisoId;
    public int $UserId;
    public bool $Ver;
    public bool $Editar;

    public function __construct()
    {
        return "construct function was initialized.";
    }
}
