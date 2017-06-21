<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Listeners\AuthTokenCreatingListener;

class AuthToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token', 'valid_until', 'user_id'
    ];
    
    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $events = [
        'creating' => AuthTokenCreatingListener::class
    ];
}
