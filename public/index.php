<?php

use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$app = new \Slim\App([
    "settings" => [
        "displayErrorDetails" => true
    ]
]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new Logger('logger');

    $logger->pushHandler(new StreamHandler('../logs/app.log'));

    return $logger;
};

$container['errorHandler'] = function ($container) {
    return function (Request $request, Response $response, Exception $exception) use ($container) {
        error_log($exception);
        $container->logger->error("Error", [$exception]);

        $error = [
            "success" => false,
            "error" => $exception->getMessage()
        ];

        return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($error));
    };
};

$container['db'] = function () {
    return new Medoo([
        'database_type' => 'mysql',
        'database_name' => getenv("DB_NAME"),
        'server' => getenv("DB_HOST"),
        'username' => getenv("DB_USER"),
        'password' => getenv("DB_PASSWORD")
    ]);
};

$app->get('/', function (Request $request, Response $response, array $args) {

    $data = $this->db->select('account', ['id', 'name']);

    return $response->write(json_encode($data));

});

$app->run();