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
        $now = new \DateTime();
        $now->modify('+2 hours');
        $token->valid_until = $now->format('Y-m-d H:i:s');
        $token->user_id = $user->id;
        $token->save();
        \App\AuthToken::where('user_id', $user->id)->where('token', '<>', $token->token)->delete();
        
        return $token;
    }
}
