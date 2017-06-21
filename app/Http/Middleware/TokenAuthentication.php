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
                    $this->messageFormatter->formatErrorMessage(
                            $validation->messages(), 
                            ResponseErrorCode::VALIDATION_FAILED,
                            $statusCode),
                    $statusCode
                );
        }
        
        $authToken = \App\AuthToken::where('token', $request['auth_token'])->first();
        $user = \App\User::where('id', $authToken->user_id);
        
        if(!$authToken || !$user) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(
                            ['auth_token' => 'This token is invalid.'], 
                            ResponseErrorCode::INVALID_TOKEN,
                            $statusCode),
                    $statusCode
                );
        }
        
        $now = new \DateTime();
        $validityDate = new \DateTime($authToken->valid_until);
        if($now > $validityDate) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(
                            ['auth_token' => 'This token has expired..'],
                            ResponseErrorCode::EXPIRED_TOKEN,
                            $statusCode),
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
