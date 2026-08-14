<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\UserController;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Utilities\Config;
use App\Utilities\ResponseHelper;

$allowedOrigins = ['https://your-frontend-domain.com', 'http://localhost:3000']; 
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $requestMethod = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? null;
    $requestHeaders = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? null;

    if ($requestMethod) {
        header('Access-Control-Allow-Methods: ' . $requestMethod);
    }
    if ($requestHeaders) {
        header('Access-Control-Allow-Headers: ' . $requestHeaders);
    }

    header('Access-Control-Max-Age: 86400');

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
    $userService = new UserService($userRepo);
    $userController = new UserController($userService);

    switch ($resource) {
        case 'user':
            switch ($action) {
                case 'register':
                case 'login':
                    if ($method === 'POST') {
                        $rawInput = file_get_contents('php://input');
                        $data = json_decode($rawInput, true);

                        if ($rawInput === '' || ($data === null && json_last_error() !== JSON_ERROR_NONE)||!is_array($data)) {
                            ResponseHelper::sendResponse(400, ['message' => 'Invalid JSON or empty body']);
                        }

                        if ($action === 'register') {
                            $userController->register($data);
                        } else {
                            $userController->login($data);
                        }
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