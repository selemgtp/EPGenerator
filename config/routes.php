<?php
/**
 *
 * @package default
 */

declare(strict_types=1);
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Exception\HttpNotFoundException;
use EndpointsApi\Actions\Specification as Specification;
use EndpointsApi\EndpointsApi\Genericos\GenericSelect as GenericosGenericSelect;

return function (App $app) {
    $app->group("/genericos", function (Group $group) {
        $group->get(
            "/genericselect",
            GenericosGenericSelect::class . ":execute"
        );
    });

    $app->get("/specification", Specification::class . ":yaml");

    $app->map(
        ["GET", "POST", "PUT", "DELETE", "PATCH"],
        "/{routes:.+}",
        function ($request, $response) {
            throw new HttpNotFoundException($request);
        }
    );
};
?>
