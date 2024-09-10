<?php

namespace App\Http\Responses;

class UsersResponse
{
    public int $Id;
    public string $Nombres;
    public string $Apellidos;
    public string $Telefono1;
    public string $Telefono2;
    public bool $Login;
    public string $Username;
    public string $Password;
    public int $Ruta;
    public string $Email;
    public int $Rol;
    public Array $roles_permiso;

    public function __construct()
    {
        return "construct function was initialized.";
    }
}
