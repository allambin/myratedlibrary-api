<?php

namespace App\Libraries\Api;

class MessageFormatter
{
    public function formatErrorMessage($errors, $errorCode = null, $code = 400)
    {
        switch ($errorCode) {
            case ResponseErrorCode::VALIDATION_FAILED:
                $message = "Validation failed";
                break;
            case ResponseErrorCode::LOGIN_FAILED:
                $message = "Login failed";
                break;
            default:
                $message = "Unknown error";
                break;
        }
        
        return [
            'code' => $code,
            'message' => $message,
            'errors' => $errors
        ];
    }
}
