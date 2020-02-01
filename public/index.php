<?php

use Medoo\Medoo;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'myconfig');
$dotenv->load();

$app = new \Slim\App;

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new Logger('my_logger');
    $file_handler = new StreamHandler('../logs/app.log');
    $firephp = new FirePHPHandler();
    $logger->pushHandler($file_handler);
    $logger->pushHandler($firephp);
    return $logger;
};

$container['database'] = function () {
    return new Medoo([
        'database_type' => 'mysql',
        'database_name' => getenv("DB_NAME"),
        'server' => getenv("DB_HOST"),
        'username' => getenv("DB_USER"),
        'password' => getenv("DB_PASSWORD")
    ]);
};

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    $this->logger->info('Hello ', array('username' => $name));

    return $response;
});

$app->run();