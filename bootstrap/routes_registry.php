<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\Registration\RegisterController;
use App\Controllers\Registration\ShowRegistrationController;
use App\Controllers\Registration\VerifyEmailController;
use Velo\Middlewares\AntiCsrf\AntiCsrfMiddleware;
use Velo\Router\Router\Router;

/**
 * @var Router $router
 */

$router->get('/', HomeController::class);
$router->get('/register', ShowRegistrationController::class);
$router->post('/register', RegisterController::class)
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->get('/verify-email/{token}', VerifyEmailController::class);
