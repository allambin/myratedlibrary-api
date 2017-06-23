<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use AuthByToken;
use App\Book;
use App\Libraries\Api\MessageFormatter;
use App\Libraries\Api\ResponseErrorCode;

class BookController extends Controller
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
        $validation = $this->validator($request->all(), 'create');
        if ($validation->fails()) {
            return response()->json(
                    $this->messageFormatter->formatErrorMessage(ResponseErrorCode::VALIDATION_FAILED, $validation->messages()),
                    400
                );
        }
        
        $book = new Book($request->all(), ['except' => ['auth_token', 'authors']]);
        $book->user_id = AuthByToken::user(\App\AuthToken::where('token', $request['auth_token'])->firstOrFail())->id;
        $book->save();
        
        $authors = $this->getAuthorsFromRequest($request);
        $book->authors()->saveMany($authors);
        
        return response()->json($book->formatJson(), 200);
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
        
        $book = \App\Book::find($id);
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
        
        $book->fill($request->all(), ['except' => ['auth_token', 'authors']]);
        $book->save();
        
        if(!empty($request['authors'])) {
            $book->authors()->detach();
            $authors = $this->getAuthorsFromRequest($request);
            $book->authors()->saveMany($authors);
        }
        
        return response()->json($book->formatJson(), 200);
    }
    
    /**
     * Get a validator for an incoming login request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $scenario = null)
    {
        switch ($scenario) {
            case 'create':
                $rules = [
                    'title' => 'required',
                    'authors' => 'required'
                ];
                break;
            default:
                $rules = [
                    'title' => 'required'
                ];
                break;
        }
        
        return Validator::make($data, $rules);
    }
    
    /**
     * 
     * @param Request $request
     */
    protected function getAuthorsFromRequest(Request $request)
    {
        $authors = [];
        if(is_array($request['authors'])) {
            foreach ($request['authors'] as $name) {
                $author = \App\Author::where('name', $name)->first();
                if(!$author) {
                    $authors[] = new \App\Author([
                        'name' => $name
                    ]);
                } else {
                    $authors[] = $author;
                }
            }
        } else {
            $authors[] = new \App\Author([
                'name' => $request['authors']
            ]);
        }
        
        return $authors;
    }
}
