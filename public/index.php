<?php
declare(strict_types=1);

use Velo\Core\App;
use Velo\Core\DefaultContainerFactory;
use Velo\Core\ThrowableHandling\ThrowableHandler;
use Velo\Database\PdoFactory;
use Velo\Http\Request;
use Velo\Logger\Logger;
use Velo\Logger\NativeLogTextFormatter;
use Velo\Router\Router\Router;
use Velo\FileSystem\PathResolver\PathResolver;

require __DIR__ . '/../vendor/autoload.php';

$container = DefaultContainerFactory::create();

$pathResolver = $container->get(PathResolver::class);
$pathResolver->setDirPath(PathResolver::VIEWS_DIR_KEY, __DIR__ . '/../views/');
$pathResolver->setErrorFilePath(404, 'errors/error404.php');
$pathResolver->setErrorFilePath(403, 'errors/error_general.php');
$pathResolver->setErrorGeneralFilePath('errors/error_general.php');

if (!isset($_ENV['DB_HOST'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(realpath(__DIR__ . '/../'));
    $dotenv->load();
}

$container->set(PDO::class, static fn(): PDO => PdoFactory::create(
    host: $_ENV['DB_HOST'],
    sql: 'mysql',
    port: (int)$_ENV['DB_PORT'],
    dbname: $_ENV['DB_NAME'],
    username: $_ENV['DB_USER'],
    password: $_ENV['DB_USER_PASS']
));

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