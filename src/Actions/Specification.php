<?php

/**
 *
 */

declare(strict_types=1);

namespace EndpointsApi\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 *
 */
class Specification
{
    /**
     *
     *
     * @param Request $request
     * @param Response $response
     */
    public function yaml(Request $request, Response $response, $args)
    {
        $path = __DIR__ . '/..'; 
        $openapi = \OpenApi\scan($path);
        $response->getBody()->write($openapi->toYaml());
        return $response
            ->withHeader('Content-Disposition', 'attachment; filename="specification.yml"')
            ->withHeader('Content-Type', 'application/x-yaml')
            ->withStatus(200);
    }
}
