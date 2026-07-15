<?php
require_once __DIR__ . '/../repos/users.php';

class UserController
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    public function register(array $data): void
    {
        try {
            if (empty($data['username']) || empty($data['password'])) {
                ResponseHelper::sendResponse(422, ['message' => 'Needs username and password.']);
            }
            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                ResponseHelper::sendResponse(422, ['message' => 'Username may only contain letters, numbers, and underscores.']);
            }
            if (strlen($username) < 6) {
                ResponseHelper::sendResponse(422, ['message' => 'Invalid username format.']);
            }
            if (strlen($password) < 6) {
                ResponseHelper::sendResponse(422, ['message' => 'Password must be at least 6 characters.']);
            }

            $this->repo->create($username, $password);
            ResponseHelper::sendResponse(201, ['message' => 'Registration successful.']);
        } catch (\PDOException $e) {
            if ($e->getCode() === 23000) {
                ResponseHelper::sendResponse(409, ['message' => 'Username already taken.']);
            }
            error_log('PDO error during registration: ' . $e->getMessage());
            ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
        } catch (\Exception $e) {
            error_log('Unexpected error during registration: ' . $e->getMessage());
            ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
        }
    }

    public function login(array $data): void
    {
        if (empty($data['username']) || empty($data['password'])) {
            ResponseHelper::sendResponse(422, ['message' => 'Needs username and password.']);
        }
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');
        try {
            $user = $this->repo->findByUsernameAndPassword($username, $password);
            if ($user) {
                $token = JwtHelper::generateToken([
                    'user_id'  => $user['id'],
                    'username' => $user['username']
                ]);
                ResponseHelper::sendResponse(200, [
                    'message'    => 'Login successful.',
                    'token'      => $token,
                    'expires_in' =>  Config::getInt('JWT_EXPIRY', 3600),
                    'user'       => [
                        'id'       => $user['id'],
                        'username' => $user['username']
                    ]
                ]);
            } else {
                ResponseHelper::sendResponse(401, ['message' => 'Invalid username or password.']);
            }
        } catch (\Exception $e) {
            error_log('Unexpected error during login: ' . $e->getMessage());
            ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
        }
    }

    public function me(): void
    {
        try {
            $auth = AuthHelper::getAuthenticatedUser();
            $user = $this->repo->findById($auth['user_id']);
            if ($user) {
                ResponseHelper::sendResponse(200, $user);
            } else {
                ResponseHelper::sendResponse(404, ['message' => 'User not found.']);
            }
        } catch (\Exception $e) {
            error_log('Unexpected error during authentication: ' . $e->getMessage());
            ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
        }
    }
}
