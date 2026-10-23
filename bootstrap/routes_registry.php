<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\RegistryController;
use Velo\Router\Router\Router;

/**
 * @var Router $router
 */

$router->get('/', HomeController::class);
$router->get('/register', RegistryController::class, 'showRegister');
