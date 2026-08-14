<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use App\Utilities\ResponseHelper;
use App\Utilities\AuthHelper;
use App\Exceptions\ValidationException;
use App\Exceptions\DuplicateUserException;
use App\Exceptions\AuthenticationException;
use App\Exceptions\UnauthorizedException;

class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(array $data): void
    {
        try {
            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            $this->userService->register($username, $password);
            ResponseHelper::sendResponse(201, ['message' => 'Registration successful.']);
        } catch (ValidationException $e) {
            ResponseHelper::sendResponse(422, ['message' => $e->getMessage()]);
        } catch (DuplicateUserException $e) {
            ResponseHelper::sendResponse(409, ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
        }
    }

    public function login(array $data): void
    {
        try {
            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            $result = $this->userService->login($username, $password);
            ResponseHelper::sendResponse(200, array_merge(['message' => 'Login successful.'], $result));
        } catch (AuthenticationException $e) {
            ResponseHelper::sendResponse(401, ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            ResponseHelper::sendResponse(422, ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
        }
    }

    public function me(): void
    {
        try {
            $auth = AuthHelper::getAuthenticatedUser();
            $user = $this->userService->getUserProfile($auth['user_id']);
            if ($user) {
                ResponseHelper::sendResponse(200, $user);
            } else {
                ResponseHelper::sendResponse(404, ['message' => 'User not found.']);
            }
        } catch (UnauthorizedException $e) {
            ResponseHelper::sendResponse(401, ['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Profile error: ' . $e->getMessage());
            ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
        }
    }
}
