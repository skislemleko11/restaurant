<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use Velo\Router\Router\Router;

/**
 * @var Router $router
 */

$router->get('/', HomeController::class);
