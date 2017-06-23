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
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::VALIDATION_FAILED, $validation->messages()),
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
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::VALIDATION_FAILED, $validation->messages()),
                    400
                );
        }
        
        $library = \App\Library::find($id);
        if(!$library) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::NOT_FOUND, 'library'),
                    400
                );
        }
        
        if(!$this->isUserAuthorizedToUpdate($request['auth_token'], $library)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::UNAUTHORIZED, 'library'),
                    401
                );
        }
        
        $library->fill($request->all(), ['except' => ['auth_token']]);
        $library->save();
        
        return response()->json($library, 200);
    }
    
    /**
     * Patch the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function patch(Request $request, $id)
    {
        if(!in_array('is_public', array_keys($request->all()))) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::IMMUTABLE_FIELD, 'library'),
                    400
                );
        }
        
        $library = \App\Library::find($id);
        $originalLibrary = clone $library;
        if(!$library) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::NOT_FOUND, 'library'),
                    400
                );
        }
        
        if(!$this->isUserAuthorizedToUpdate($request['auth_token'], $library)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::UNAUTHORIZED, 'library'),
                    400
                );
        }
        
        $library->is_public = $request['is_public'];
        $library->save();
        
        return response()->json(['original' => $originalLibrary, 'patched' => $library], 200);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $library = \App\Library::find($id);
        if(!$library) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::NOT_FOUND, 'library'),
                    404
                );
        }
        
        if(!$this->isUserAuthorizedToUpdate($request['auth_token'], $library)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::UNAUTHORIZED, 'library'),
                    401
                );
        }
        
        $library->delete();

        return response(null, 204);
    }
    
    /**
     * Get a validator for an incoming request.
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
    
    /**
     * 
     * @param string $authToken
     * @param Library $library
     * @return bool
     */
    protected function isUserAuthorizedToUpdate($authToken, \App\Library $library)
    {
        $user = AuthByToken::user(\App\AuthToken::where('token', $authToken)->firstOrFail());
        return $user->canEditLibrary($library);
    }
}
