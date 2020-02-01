<?php

namespace VIPSystem;


use Exception;
use Medoo\Medoo;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use wpscholar\phpdotenv\Loader;

class App {

    private $app;

    /**
     * App constructor.
     */
    public function __construct()
    {

        $loader = new Loader();
        $loader->parse(__DIR__ . '/../.env')->load();

        $this->app = new \Slim\App([
            "settings" => [
                "displayErrorDetails" => true
            ]
        ]);

        $container = $this->app->getContainer();

        $container['logger'] = function($c) {
            $logger = new Logger('logger');

            $logger->pushHandler(new StreamHandler('../logs/app.log'));

            return $logger;
        };

        $container['errorHandler'] = function ($container) {
            return function (Request $request, Response $response, Exception $exception) use ($container) {
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

        $container['db'] = function ($c) {
            $c->logger->info("DB_NAME: " . getenv("DB_HOST"));
            return new Medoo([
                'database_type' => 'mysql',
                'database_name' => getenv("DB_NAME"),
                'server' => getenv("DB_HOST"),
                'username' => getenv("DB_USER"),
                'password' => getenv("DB_PASSWORD")
            ]);
        };
    }


    /**
     * @return \Slim\App
     */
    public function getSlimApp() : \Slim\App
    {
        return $this->app;
    }


}