<?php


namespace VIPSystem\Controllers;


use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use VIPSystem\Exceptions\MissingPasswordException;
use VIPSystem\Exceptions\MissingUsernameException;
use VIPSystem\Exceptions\UserNotFoundException;
use VIPSystem\Models\User;

class UserController
{
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function list(Request $request, Response $response, $args) {
        $jwt = $request->getAttribute("token");

        $id = (int) $jwt["user"]->user_id;

        $user = $this->getUserByID($id);

        $json = [
            "success" => true,
            "results" => $user->toArray()
        ];

        return $response->withJson($json);
    }

    public function get(Request $request, Response $response, $args) {
        $data = $this->container->get('db')->get('user', '*', [
            "user_id" => $args['id']
        ]);

        if (count($data) > 0) {
            // SELECT package_name FROM package LEFT JOIN user_package ON user_package.package_id = package.package_id WHERE user_package.user_id = 1
            $data['packages'] = $packages = $this->container->get('db')->select(
            // Table name
                'package',

                // Join
                [
                    // The row package_id from packages is equal to package_id of user_package
                    "[>]user_package" => "package_id"
                ],

                // Columns
                [
                    "package.package_id",
                    "package.package_name"
                ],

                // WHERE
                [
                    "user_package.user_id" => $args['id']
                ]
            );

            $json = [
                "success" => true,
                "results" => $data
            ];

            return $response->withJson($json);
        }
        else
        {
            $json = [
                "success" => false,
                "error" => "User not found"
            ];

            return $response
                ->withStatus(404)
                ->withJson($json);
        }
    }

    /**
     * Generates a JSON web token if credentials are correct
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws MissingUsernameException
     * @throws MissingPasswordException
     * @throws UserNotFoundException
     */
    public function authenticate(Request $request, Response $response, $args) {

        $body = $request->getParsedBody();
        $key = getenv("JWT_SECRET");

        if (!isset($body["user_name"])) throw new MissingUsernameException("Missing username");
        if (!isset($body["password"])) throw new MissingPasswordException("Missing password");

        $user = $this->getUserByCredentials($body["user_name"], $body["password"]);

        $payload = array(
            "user" => [
                "user_id" => $user->getUserId()
            ]
        );

        $data = [
            "success" => true,
            "results" => [
                "token" => JWT::encode($payload, $key)
            ]
        ];

        return $response->withJson($data);

    }

    /**
     * Verifies the token
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function verify(Request $request, Response $response, $args) {
        $jwt = $request->getAttribute("token");

        $data = [
            "success" => true,
            "results" => [
                "jwt" => $jwt
            ]
        ];

        return $response->withJson($data);
    }

    /**
     * @param String $user_name
     * @param String $password
     * @return User
     * @throws UserNotFoundException
     */
    private function getUserByCredentials(String $user_name, String $password) : User {

        // TODO: Add password handling
        $data = $this->container->get("db")->select("user", "*", [
            "user_name" => $user_name
        ]);

        if (sizeof($data) < 1) throw new UserNotFoundException("Invalid credentials or user does not exist");

        // TODO Remove this when password is handled
        if ($password !== "1234") throw new UserNotFoundException("Invalid credentials or user does not exist");

        return new User($data);

    }

    /**
     * @param int $user_id
     * @return User
     * @throws UserNotFoundException
     */
    private function getUserByID(int $user_id) : User {

        $data = $this->container->get("db")->select("user", "*", [
            "user_id" => $user_id
        ]);

        if (sizeof($data) < 1) throw new UserNotFoundException("user not found");

        return new User($data);

    }

}