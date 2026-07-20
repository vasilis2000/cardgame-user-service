<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Middleware\AuthMiddleware;

class AuthHelper
{
    public static function getAuthenticatedUser(): array
    {
        $decoded = AuthMiddleware::authenticate();
        if (!isset($decoded->user_id) || !isset($decoded->username)) {
            ResponseHelper::sendResponse(401, ['message' => 'Invalid token payload.']);
        }
        $userId = $decoded->user_id;
        $username = $decoded->username;
        if (!$userId) {
            ResponseHelper::sendResponse(401, ['message' => 'Invalid token payload.']);
        }
        return ['user_id' => $userId, 'username' => $username];
    }
}
