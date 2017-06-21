<?php

namespace App\Listeners;

use App\Events\UserCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class UserCreatingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->user->password_hash = password_hash($this->user->password, PASSWORD_DEFAULT);
        unset($this->user->password);
    }
}
