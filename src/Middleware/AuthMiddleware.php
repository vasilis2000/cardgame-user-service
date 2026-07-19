<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\JwtHelper;
use App\Helpers\ResponseHelper;

class AuthMiddleware
{
    public static function authenticate(): ?object
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            ResponseHelper::sendResponse(401, ['error' => 'No token provided']);
        }

        $token = $matches[1];
        $decoded = JwtHelper::validateToken($token);

        if (!$decoded) {
            ResponseHelper::sendResponse(401, ['error' => 'Invalid or expired token']);
        }

        return $decoded;
    }
}
