<?php

declare(strict_types=1);

namespace App;

use App\Controllers\UserController;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Http\Request;
use App\Http\Response;
use App\Exceptions\ValidationException;
use App\Exceptions\DuplicateUserException;
use App\Exceptions\AuthenticationException;
use App\Exceptions\UnauthorizedException;

class Router
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function dispatch(): void
    {
        $exceptionMap = [
            ValidationException::class     => 422,
            DuplicateUserException::class  => 409,
            AuthenticationException::class => 401,
            UnauthorizedException::class   => 401,
        ];

        try {
            $segments = $this->request->getSegments();
            $resource = $segments[0] ?? '';
            $action   = $segments[1] ?? null;
            $method   = $this->request->getMethod();

            switch ($resource) {
                case 'user':
                    $this->handleUserRoutes($action, $method);
                    break;

                default:
                    Response::error(404, 'Not Found');
            }
        } catch (\Throwable $e) {
            $this->handleException($e, $exceptionMap);
        }
    }

    private function handleUserRoutes(?string $action, string $method): void
    {
        $userRepo = new UserRepository();
        $userService = new UserService($userRepo);
        $userController = new UserController($userService);

        switch ($action) {
            case 'register':
            case 'login':
                if ($method !== 'POST') {
                    Response::error(405, 'Method Not Allowed.');
                    return;
                }
                $username = $this->request->getJsonString('username') ?: '';
                $password = $this->request->getJsonString('password') ?: '';
                if ($action === 'register') {
                    $userController->register($username, $password);
                } else {
                    $userController->login($username, $password);
                }
                break;

            case 'me':
                if ($method !== 'GET') {
                    Response::error(405, 'Method Not Allowed.');
                    return;
                }
                $userController->me();
                break;

            default:
                Response::error(404, 'Not Found');
        }
    }

    private function handleException(\Throwable $e, array $exceptionMap): void
    {
        $status = $exceptionMap[get_class($e)] ?? 500;
        $message = ($status === 500) ? 'Internal server error.' : $e->getMessage();
        error_log('Request error: ' . (string) $e);
        Response::error($status, $message);
    }
}