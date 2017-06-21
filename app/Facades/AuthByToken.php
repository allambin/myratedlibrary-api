<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AuthByToken extends Facade
{
    protected static function getFacadeAccessor() 
    {
        return 'App\Libraries\Api\AuthByToken'; 
    }
}
