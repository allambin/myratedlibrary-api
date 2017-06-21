<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\Api\MessageFormatter;
use App\Libraries\Api\ResponseErrorCode;
use Validator;

class LoginController extends Controller
{
    public function __construct(MessageFormatter $messageFormatter)
    {
        $this->messageFormatter = $messageFormatter;
    }
    
    /**
     * Handle a login request for the API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request, \App\Libraries\Api\AuthTokenGenerator $authTokenGenerator)
    {
        $validation = $this->validator($request->all());
        if ($validation->fails()) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage($validation->messages(), ResponseErrorCode::VALIDATION_FAILED),
                    400
                );
        }
        
        $user = \App\User::where('email', $request['email'])->first();
        if (!$user || password_verify($request['password'], $user->password_hash) === false) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(['user' => 'Wrong email/password.'], ResponseErrorCode::LOGIN_FAILED),
                    401
                );
        }
        
        $token = $authTokenGenerator->generate($user);
        return response()->json(['auth_token' => $token->token], 200);
    }
    
    /**
     * Get a validator for an incoming login request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => 'required',
            'password' => 'required',
        ]);
    }
}
