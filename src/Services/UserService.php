<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utilities\UserValidator;
use App\Utilities\JwtHelper;
use App\Utilities\Config;
use App\Exceptions\ValidationException;
use App\Exceptions\DuplicateUserException;
use App\Exceptions\AuthenticationException;
use PDOException;

class UserService
{
    private UserRepository $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    public function register(string $username, string $password): void
    {
        $this->validateCredentials($username, $password);

        try {
            $this->repo->create($username, $password);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DuplicateUserException('Username already taken.');
            }
            throw $e;
        }
    }

    public function login(string $username, string $password): array
    {
        $this->validateCredentials($username, $password);

        $user = $this->repo->findByUsernameAndPassword($username, $password);
        if (!$user) {
            throw new AuthenticationException('Invalid username or password.');
        }

        $token =  JwtHelper::generateToken([
            'user_id'  => $user['id'],
            'username' => $user['username'],
        ]);

        return [
            'token'      => $token,
            'expires_in' => Config::getInt('JWT_EXPIRY', 3600),
            'user'       => [
                'id'       => $user['id'],
                'username' => $user['username'],
            ],
        ];
    }

    public function getUserProfile(int $userId): ?array
    {
        return $this->repo->findById($userId);
    }

    private function validateCredentials(string $username, string $password): void
    {
        $validation = UserValidator::validateUser(['username' => $username, 'password' => $password]);
        if (!$validation['valid']) {
            throw new ValidationException(implode(' ', $validation['errors']));
        }
    }
}
