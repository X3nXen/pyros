<?php
header('Access-Control-Allow-Origin: *'); // Fejlesztéskor mehet a *, élesben a React app domainje
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('CONTROLLER_PATH', __DIR__ . '/controllers/');

function handleRequest($url)
{
    $url = preg_replace('/[^a-zA-Z0-9\/]/', '', $url);
    $url = ltrim($url, '/');
    $parts = explode('/', $url);

    $controller = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'IndexController';
    $method = !empty($parts[1]) ? $parts[1] : 'index';
    $args = array_slice($parts, 2);

    $file_path = CONTROLLER_PATH . strtolower($controller) . '.php';

    if (file_exists($file_path)) {
        require_once $file_path;

        if (class_exists($controller)) {
            $controller_instance = new $controller();

            if (method_exists($controller_instance, $method)) {
                call_user_func_array([$controller_instance, $method], $args);

            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Path not found']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Route not found']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
}

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = urldecode($request_uri);

$base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($base_dir !== '/') {
    $request_uri = preg_replace('#^' . preg_quote($base_dir) . '#', '', $request_uri);
}

handleRequest($request_uri);