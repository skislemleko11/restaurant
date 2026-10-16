<?php
declare(strict_types=1);

use Velo\Core\App;
use Velo\Core\DefaultContainerFactory;
use Velo\Core\ThrowableHandling\ThrowableHandler;
use Velo\Http\Request;
use Velo\Logger\Logger;
use Velo\Logger\NativeLogTextFormatter;
use Velo\Router\Router\Router;

require __DIR__ . '/../vendor/autoload.php';

$container = DefaultContainerFactory::create();

// Set up Logger
$logFilePath = __DIR__ . '/../logs/app.log';
$logger = new Logger($container->get(NativeLogTextFormatter::class), $logFilePath);
$container->set(Logger::class, $logger);

// Set up ThrowableHandler
/**
 * @var ThrowableHandler $throwableHandler
 */
$throwableHandler = $container->get(ThrowableHandler::class);
$throwableHandler->setAsGlobalExceptionAndErrorHandler();

// Set up Router
/**
 * @var Router $router
 */
$router = $container->get(Router::class);
$routesRegistryFile = __DIR__ . '/../bootstrap/routes_registry.php';
$router->loadRoutesFromRegistryFile($routesRegistryFile);

// Run the application
new App($router, $container)->run(Request::fromGlobals());