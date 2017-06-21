<?php

namespace App\Libraries\Api;

class AuthByToken
{    
    public function user(\App\AuthToken $authToken)
    {
        return \App\User::where('id', $authToken->user_id)->first();
    }
}
