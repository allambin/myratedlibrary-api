<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use AuthByToken;
use App\Library;
use App\Libraries\Api\MessageFormatter;
use App\Libraries\Api\ResponseErrorCode;

class LibraryController extends Controller
{
    public function __construct(MessageFormatter $messageFormatter)
    {
        $this->middleware('token.auth', ['except' => ['index']]);
        $this->messageFormatter = $messageFormatter;
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = $this->validator($request->all());
        if ($validation->fails()) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage($validation->messages(), ResponseErrorCode::VALIDATION_FAILED),
                    400
                );
        }
        
        $library = new Library($request->all(), ['except' => ['auth_token']]);
        $library->user_id = AuthByToken::user(\App\AuthToken::where('token', $request['auth_token'])->firstOrFail())->id;
        $library->save();
        
        return response()->json($library, 200);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = $this->validator($request->all());
        if ($validation->fails()) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage($validation->messages(), ResponseErrorCode::VALIDATION_FAILED),
                    400
                );
        }
        
        $library = \App\Library::find($id);
        if(!$library) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(['library' => 'Not found.'], ResponseErrorCode::NOT_FOUND),
                    400
                );
        }
        
        $user = AuthByToken::user(\App\AuthToken::where('token', $request['auth_token'])->firstOrFail());
        if(!$user->canEditLibrary($library)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(['library' => 'Unauthorized action.'], ResponseErrorCode::UNAUTHORIZED),
                    400
                );
        }
        
        $library->fill($request->all(), ['except' => ['auth_token']]);
        $library->save();
        
        return response()->json($library, 200);
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
            'name' => 'required'
        ]);
    }
}
