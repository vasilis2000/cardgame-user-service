<?php

declare(strict_types=1);

namespace App\Utilities;

use App\Middleware\AuthMiddleware;
use App\Exceptions\UnauthorizedException;

class AuthHelper
{
    public static function getAuthenticatedUser(): array
    {
        $decoded = AuthMiddleware::authenticate();
        if (!isset($decoded->user_id) || !isset($decoded->username)) {
            throw new UnauthorizedException('Invalid token payload.');
        }
        return [
            'user_id'  => $decoded->user_id,
            'username' => $decoded->username,
        ];
    }
}
