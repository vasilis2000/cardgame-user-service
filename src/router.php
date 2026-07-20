<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Controllers\UserController;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Helpers\JwtHelper;
use App\Helpers\Config;
use App\Helpers\ResponseHelper;

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


try {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = trim($requestUri, '/');
    $segments = $requestUri ? explode('/', $requestUri) : [];

    $resource = $segments[0] ?? '';
    $action   = $segments[1] ?? null;
    $method   = $_SERVER['REQUEST_METHOD'];
        
    Config::load();

    $userRepo = new UserRepository();
    $jwtHelper = new JwtHelper();
    $userService = new UserService($userRepo, $jwtHelper);
    $userController = new UserController($userService);

    switch ($resource) {
        case 'user':
            switch ($action) {
                case 'register':
                    if ($method === 'POST') {
                        $data = json_decode(file_get_contents('php://input'), true) ?? [];
                        $userController->register($data);
                    } else {
                        ResponseHelper::sendResponse(405, ['message' => 'Method Not Allowed.']);
                    }
                    break;
                case 'login':
                    if ($method === 'POST') {
                        $data = json_decode(file_get_contents('php://input'), true) ?? [];
                        $userController->login($data);
                    } else {
                        ResponseHelper::sendResponse(405, ['message' => 'Method Not Allowed.']);
                    }
                    break;
                case 'me':
                    if ($method === 'GET') {
                        $userController->me();
                    } else {
                        ResponseHelper::sendResponse(405, ['message' => 'Method Not Allowed.']);
                    }
                    break;
                default:
                    ResponseHelper::sendResponse(404, ['message' => 'Not Found']);
                    break;
            }
            break;
        default:
            ResponseHelper::sendResponse(404, ['message' => 'Not Found']);
            break;
    }
} catch (\Throwable $e) {
    error_log('Unhandled router error: ' . $e->getMessage());
    ResponseHelper::sendResponse(500, ['message' => 'Internal server error.']);
}