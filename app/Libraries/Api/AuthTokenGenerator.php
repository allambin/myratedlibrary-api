<?php

namespace App\Libraries\Api;


class AuthTokenGenerator
{
    /**
     * Generate an authentication token for user
     * @param \App\User $user
     * @return \App\AuthToken
     */
    public function generate(\App\User $user)
    {
        $token = new \App\AuthToken();
        $token->token = sha1($user->email . time());
        $token->user_id = $user->id;
        $token->save();
        \App\AuthToken::where('user_id', $user->id)->where('token', '<>', $token->token)->delete();
        
        return $token;
    }
}
