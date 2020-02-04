<?php

namespace VIPSystem;


use Exception;
use Medoo\Medoo;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Tuupola\Middleware\JwtAuthentication;
use VIPSystem\Controllers\PackageController;
use VIPSystem\Controllers\UserController;
use VIPSystem\Middleware\CRSMiddleware;
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

                $errormsg = $exception->getMessage();
                $errormsg = str_replace('"', "", $errormsg);
                $errormsg = str_replace("'", "", $errormsg);


                $container->logger->error($errormsg);

                $error = [
                    "success" => false,
                    "error" => $errormsg
                ];

                return $response->withStatus(500)
                    ->withJson($error);
            };
        };

        $container['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {

                $error = [
                    "success" => false,
                    "error" => "API Route does not exist."
                ];

                return $response->withStatus(404)
                    ->withJson($error);
            };
        };

        $container['db'] = function ($c) {
            return new Medoo([
                'database_type' => 'mysql',
                'database_name' => getenv("DB_NAME"),
                'server' => getenv("DB_HOST"),
                'username' => getenv("DB_USER"),
                'password' => getenv("DB_PASSWORD")
            ]);
        };

        $container['UserController'] = function($container) {
            $database = $container->get("db"); // retrieve the 'view' from the container
            return new UserController($database);
        };

        $this->app->add(new CRSMiddleware);

        $this->app->add(new JwtAuthentication([
            "secret" => getenv("JWT_SECRET"),
            "ignore" => ["/users/authenticate"],
            "error" => function ($response, $arguments) {
                $data = [
                    "success" => false,
                    "error" => $arguments["message"]
                ];
                return $response
                    ->withStatus(401)
                    ->withJson($data);
            }
        ]));

        $this->app->group('/users', function (\Slim\App $app) {
            $app->get('', UserController::class . ':list');
            $app->get('/{id:[0-9]+}', UserController::class . ':get');
            $app->get('/authenticate', UserController::class . ':authenticate');
            $app->get('/verify', UserController::class . ':verify');
        });

        $this->app->get('/packages', PackageController::class . ':list');

        $this->app->get('/packages/{id:[0-9]+}', PackageController::class . ':get');
    }


    /**
     * @return \Slim\App
     */
    public function getSlimApp() : \Slim\App
    {
        return $this->app;
    }


}