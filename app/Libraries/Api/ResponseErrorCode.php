<?php

namespace App\Libraries\Api;

class ResponseErrorCode
{
    const VALIDATION_FAILED = 1;
    const LOGIN_FAILED = 2;
    const INVALID_TOKEN = 3;
    const EXPIRED_TOKEN = 4;
}
