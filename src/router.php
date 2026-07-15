<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/helpers/Config.php';
require_once __DIR__ . '/controllers/users.php';
require_once __DIR__ . '/helpers/AuthHelper.php';
require_once __DIR__ . '/helpers/JwtHelper.php';
require_once __DIR__ . '/helpers/ResponseHelper.php';

try {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = trim($requestUri, '/');
    $segments = $requestUri ? explode('/', $requestUri) : [];

    $resource = $segments[0] ?? '';
    $action   = $segments[1] ?? null;
    $method   = $_SERVER['REQUEST_METHOD'];
    Config::load();
    $userController = new UserController();
    switch ($resource) {
        case 'user':
            switch ($action) {
                case 'register':
                    if ($method === 'POST') {
                        $data     = json_decode(file_get_contents('php://input'), true) ?? [];
                        $userController->register($data);
                    } else {
                        ResponseHelper::sendResponse(405, ['message' => 'Method Not Allowed.']);
                    }
                    break;
                case 'login':
                    if ($method === 'POST') {
                        $data     = json_decode(file_get_contents('php://input'), true) ?? [];
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
    ResponseHelper::sendResponse(500, ['error' => 'Internal server error.']);
}
