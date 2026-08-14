<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utilities\JwtHelper;
use App\Exceptions\UnauthorizedException;

class AuthMiddleware
{
    public static function authenticate(): object
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new UnauthorizedException('No token provided');
        }

        $token = $matches[1];
        $decoded = JwtHelper::validateToken($token);

        if (!$decoded) {
            throw new UnauthorizedException('Invalid or expired token');
        }

        return $decoded;
    }
}
