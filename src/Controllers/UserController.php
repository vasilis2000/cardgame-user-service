<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use App\Http\Response;
use App\Utilities\AuthHelper;

class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(string $username, string $password): void
    {
        $this->userService->register($username, $password);
        Response::json(201, ['message' => 'Registration successful.']);
    }

    public function login(string $username, string $password): void
    {
        $result = $this->userService->login($username, $password);
        Response::json(200, array_merge(['message' => 'Login successful.'], $result));
    }

    public function me(): void
    {
        $auth = AuthHelper::getAuthenticatedUser();
        $user = $this->userService->getUserProfile($auth['user_id']);
        if ($user) {
            Response::json(200, $user);
        } else {
            Response::error(404, 'User not found.');
        }
    }
}