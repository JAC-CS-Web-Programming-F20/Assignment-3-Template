<?php

require_once('../vendor/autoload.php');

use AssignmentThree\Router\Request;
use AssignmentThree\Router\Response;
use AssignmentThree\Router\Router;

$requestMethod = $_SERVER['REQUEST_METHOD'];
$queryString = $_SERVER['QUERY_STRING'];
$parameters = [];

switch ($requestMethod) {
	case 'POST':
		$parameters = $_POST;
		break;
	case 'PUT':
		parse_str(file_get_contents("php://input"), $parameters);
}

$request = new Request($requestMethod, $queryString, $parameters);
$response = new Response();

$router = new Router($request, $response);
$response = $router->dispatch();

header('Content-Type: application/json');
print json_encode($response);
