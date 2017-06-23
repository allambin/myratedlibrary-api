<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use AuthByToken;
use App\Book;
use App\Library;
use App\LibraryBooks;
use App\Libraries\Api\MessageFormatter;
use App\Libraries\Api\ResponseErrorCode;

class LibraryBooksController extends Controller
{
    public function __construct(MessageFormatter $messageFormatter)
    {
        $this->middleware('token.auth', ['except' => ['index']]);
        $this->messageFormatter = $messageFormatter;
    }
    
    /**
     * Add a book to a library.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addBook(Request $request, $id)
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
        
        $book = \App\Book::find($request['book_id']);
        if(!$book) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::NOT_FOUND, 'book'),
                    400
                );
        }
        
        $user = AuthByToken::user(\App\AuthToken::where('token', $request['auth_token'])->firstOrFail());
        if(!$user->canEditBook($book)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::UNAUTHORIZED, 'book'),
                    400
                );
        }
        
        if(!$user->canEditLibrary($library)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::UNAUTHORIZED, 'library'),
                    400
                );
        }
        
        LibraryBooks::updateOrCreate([
            'library_id' => $id,
            'book_id' => $request['book_id']
        ], [
            'library_id' => $id,
            'book_id' => $request['book_id']
        ]);
        
        return response(null, 204);
    }
    
    public function removeBook(Request $request, $id, $book_id)
    {
        $library = \App\Library::find($id);
        if(!$library) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::NOT_FOUND, 'library'),
                    404
                );
        }
        
        $book = \App\Book::find($book_id);
        if(!$book) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::NOT_FOUND, 'book'),
                    404
                );
        }
        
        $user = AuthByToken::user(\App\AuthToken::where('token', $request['auth_token'])->firstOrFail());
        if(!$user->canEditBook($book)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::UNAUTHORIZED, 'book'),
                    401
                );
        }
        
        if(!$user->canEditLibrary($library)) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::UNAUTHORIZED, 'library'),
                    401
                );
        }
        
        LibraryBooks::where([
            'library_id' => $id,
            'book_id' => $book_id
        ])->delete();
        
        return response(null, 204);
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
            'book_id' => 'required|integer'
        ]);
    }
}
