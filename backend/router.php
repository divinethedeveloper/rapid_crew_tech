<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Authorization, Accept");
header('Access-Control-Allow-Credentials: true');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
	header('Access-Control-Allow-Origin: *'); 
	header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS'); 
	header('Access-Control-Allow-Headers: token, Content-Type, Authorization'); 
	die(); 
} 


header('content-type: application/json');

require 'vendor/autoload.php';

use Controller\Controller;
use Middleware\Logger;
use Middleware\Middleware;
use Model\User\User;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\RouteCollector;
use Symfony\Component\ErrorHandler\Debug;

// try {
Debug::enable();

$router = new RouteCollector();

$middleware = new Middleware();

$user = new User();

$controller = new Controller();

require 'filters.php';

require 'endpoints.php';

$dispatcher = new Dispatcher($router->getData());

$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], str_replace('/backend', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

if (isset($response['status'])) {
    echo json_encode($response);
} elseif (isset($response[0]) and $response[0] === 'error') {
    echo json_encode([
        'status' => 'error',
        'message' => 'unable to retrive data successfully',
        'data' => [],
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'message' => 'data retrieved successfully',
        'data' => $response,
        'docs_endpoint' => $controller->docs_endpoint,
        'image_endpoint' => $controller->image_endpoint,
        'video_endpoint' => $controller->video_endpoint,
    ]);
}
// } catch (\Exception $e) {
//     $logger = new Logger();

//     $logger->generalLogger()->error($e->getMessage());

//     echo json_encode([
//         'status' => 'error',
//         'message' => 'System busy, try again later',
//         'error' => $e->getMessage(),
//         'exception' => $e,
//     ]);
// }
