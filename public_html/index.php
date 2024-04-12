<?php


    declare(strict_types=1);

    use EndpointsApi\Handlers\HttpErrorHandler;
    use EndpointsApi\Handlers\ShutdownHandler;
    use EndpointsApi\ResponseEmitter\ResponseEmitter;
    use DI\ContainerBuilder;
    use Slim\Factory\AppFactory;
    use Slim\Factory\ServerRequestCreatorFactory;
    //use Illuminate\Database\Capsule\Manager;

    require __DIR__ . "/../vendor/autoload.php";
    // Instantiate PHP-DI ContainerBuilder
    $containerBuilder = new ContainerBuilder();
    if (false) {
    // Should be set to true in production
        $containerBuilder->enableCompilation(__DIR__ . "/../var/cache");
    }

    // Set up settings
    $settings = require __DIR__ . "/../config/settings.php";
    $settings($containerBuilder);
    // Set up dependencies
    //$dependencies = require __DIR__ . "/../config/dependencies.php";
    //$dependencies($containerBuilder);
    // Set up repositories
    /*$repositories = require __DIR__ . "/../app/repositories.php";
    $repositories($containerBuilder);*/
    // Build PHP-DI Container instance
    $container = $containerBuilder->build();
    // Instantiate the app
    AppFactory::setContainer($container);
    $app = AppFactory::create();
    //agregar si los microservicios corren en subcarpetas
    $app->setBasePath("/endpointSql");

    /*$databases = $container->get("settings")["databases"];

    $capsule = new Manager();

    foreach ($databases as $key => $value) {
        $capsule->addConnection($databases[$key], $key);
    }

    $capsule->setAsGlobal();
    $capsule->bootEloquent();*/

    $callableResolver = $app->getCallableResolver();
    // Register middleware
    $middleware = require __DIR__ . "/../config/middleware.php";
    $middleware($app);
    // Register routes
    $routes = require __DIR__ . "/../config/routes.php";
    $routes($app);
    /** @var bool $displayErrorDetails */
    //$displayErrorDetails = $container->get("settings")["displayErrorDetails"];
    $displayErrorDetails = true;
    // Create Request object from globals
    $serverRequestCreator = ServerRequestCreatorFactory::create();
    $request = $serverRequestCreator->createServerRequestFromGlobals();
    // Create Error Handler
    $responseFactory = $app->getResponseFactory();
    $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
    // Create Shutdown Handler
    $shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
    register_shutdown_function($shutdownHandler);
    /**
     * Add Routing Middleware
     *
     * The routing middleware should be added earlier than the ErrorMiddleware
     * Otherwise exceptions thrown from it will not be handled by the middleware
     */
    $app->addRoutingMiddleware();
    /**
     * Add Error Middleware
     *
     * @param bool $displayErrorDetails -> Should be set to false in production
     * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
     * @param bool $logErrorDetails -> Display error details in error log
     * which can be replaced by a callable of your choice.
     * @param \Psr\Log\LoggerInterface $logger -> Optional PSR-3 logger to receive errors
     *
     * Note: This middleware should be added last. It will not handle any exceptions/errors
     * for middleware added after it.
     */
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
    // Run App & Emit Response
    $response = $app->handle($request);
    $responseEmitter = new ResponseEmitter();
    $responseEmitter->emit($response);


    
?>
