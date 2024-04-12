<?php
declare(strict_types=1);
use Slim\App;
use EndpointsApi\Middleware\SessionMiddleware;
use Tuupola\Middleware\CorsMiddleware;
use Tuupola\Middleware\HttpBasicAuthentication;
return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->addBodyParsingMiddleware();
    $env = require __DIR__ . "/env.php";

    $app->add(
        new HttpBasicAuthentication([
            "path" => [
                "/endpointSql/genericos",
                "/endpointSql/proyectos",
                "/endpointSql/rad",
                "/endpointSql/log",
                "/endpointSql/maestra",
                "/endpointSql/cfg",
                "/endpointSql/kpidiario",
            ],
            "ignore" => ["/endpointSql/specification"],
            "realm" => "Protected",
            "secure" => false,
            "users" => $env["basic_authentication_users"],
            //respuesta en caso de error
            "error" => function ($response, $arguments) {
                $data = [];
                $data["status"] = "error";
                $data["message"] =
                    $arguments["message"] .
                    ". Username and password not valid.";

                //respuesta con código HTTP 401 que indica la falta de acceso
                $response->getBody()->write(json_encode($data));
                return $response
                    ->withHeader("Content-Type", "application/json")
                    ->withStatus(401);
            },
        ])
    );

    $app->add(
        new CorsMiddleware([
            "origin" => ["*"],
            "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
            "headers.allow" => [
                "Authorization",
                "If-Match",
                "If-Unmodified-Since",
            ],
            "headers.expose" => ["Authorization", "Etag"],
            "credentials" => true,
            "cache" => 60,
            //respuesta de error
            "error" => function ($request, $response, $arguments) {
                $data["status"] = "error";
                $data["message"] = $arguments["message"];
                $response
                    ->getBody()
                    ->write(
                        (string) json_encode(
                            $data,
                            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                        )
                    );
                return $response
                    ->withHeader("Content-Type", "application/json")
                    ->withStatus(401);
            },
        ])
    );
};
?>
