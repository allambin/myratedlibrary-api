<?php

namespace App\Listeners;

use App\Events\UserCreating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\AuthToken;

class AuthTokenCreatingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(AuthToken $authToken)
    {
        $this->authToken = $authToken;
        $now = new \DateTime();
        $now->modify('+2 hours');
        $this->authToken->valid_until = $now->format('Y-m-d H:i:s');
    }
}
