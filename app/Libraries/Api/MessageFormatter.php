<?php

namespace App\Libraries\Api;

class MessageFormatter
{
    /**
     * 
     * @param int $errorCode
     * @param string|array $params
     * @return type
     */
    public function formatErrorMessage($errorCode, $params, $code = null)
    {
        $info = $this->getErrorDetails($errorCode);
        list($defaultCode, $message, $errors) = $info;
        
        if(!is_string($params)) {
            $errors = $params;
        } else {
            $errors = [$params => $errors];
        }
        
        return [
            'code' => is_null($code) ? $defaultCode : $code,
            'message' => $message,
            'errors' => $errors
        ];
    }
    
    protected function getErrorDetails($errorCode)
    {
        $code = 400;
        switch ($errorCode) {
            case ResponseErrorCode::VALIDATION_FAILED:
                return [
                    $code,
                    "Validation failed.",
                    "Validation failed."
                ];
            case ResponseErrorCode::LOGIN_FAILED:
                return [
                    401,
                    "Login failed.",
                    "Wrong email/password."
                ];
            case ResponseErrorCode::INVALID_TOKEN:
                return [
                    401,
                    "Login failed.",
                    "This token is invalid, please sign in."
                ];
            case ResponseErrorCode::EXPIRED_TOKEN:
                return [
                    401,
                    "Login failed.",
                    "The token has expired. Please sign in again."
                ];
            case ResponseErrorCode::UNAUTHORIZED:
                return [
                    401,
                    "You are not authorized to perform this action.",
                    "Unauthorized action."
                ];
            case ResponseErrorCode::NOT_FOUND:
                return [
                    404,
                    "The resource was not found.",
                    "Not found.",
                ];
            case ResponseErrorCode::IMMUTABLE_FIELD:
                return [
                    $code,
                    "The resource cannot be patched this way.",
                    "This field cannot be modified."
                ];
            default:
                return [
                    $code,
                    "Unknown error.",
                    "Unknown error."
                ];
        }
    }
}
