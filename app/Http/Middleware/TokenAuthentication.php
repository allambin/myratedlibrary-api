<?php

namespace App\Http\Middleware;

use Closure;
use App\Libraries\Api\MessageFormatter;
use App\Libraries\Api\ResponseErrorCode;
use Validator;
use Auth;

class TokenAuthentication
{
    public function __construct(MessageFormatter $messageFormatter)
    {
        $this->messageFormatter = $messageFormatter;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $statusCode = 401;
        $validation = $this->validator($request->all());
        if ($validation->fails()) {
            return response()->json(
                        $this->messageFormatter->formatErrorMessage(ResponseErrorCode::VALIDATION_FAILED, 
                        $validation->messages(),
                        $statusCode
                    ),
                    $statusCode
                );
        }
        
        $authToken = \App\AuthToken::where('token', $request['auth_token'])->first();
        $user = $authToken ? \App\User::where('id', $authToken->user_id) : false;
        
        if(!$authToken || !$user) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::INVALID_TOKEN, 'auth_token'),
                    $statusCode
                );
        }
        
        $now = new \DateTime();
        $validityDate = new \DateTime($authToken->valid_until);
        if($now > $validityDate) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::INVALID_TOKEN, 'auth_token'),
                    $statusCode
                );
        }
        
        return $next($request);
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
            'auth_token' => 'required'
        ]);
    }
}
