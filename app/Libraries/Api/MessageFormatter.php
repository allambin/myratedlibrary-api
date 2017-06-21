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
            case ResponseErrorCode::INVALID_TOKEN:
                $message = "Token invalid";
                break;
            case ResponseErrorCode::EXPIRED_TOKEN:
                $message = "The token has expired. Please sign in again.";
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
