<?php
declare(strict_types=1);

use App\Controllers\Auth\ActionAuthController;
use App\Controllers\Auth\ShowAuthController;
use App\Controllers\HomeController;
use App\Controllers\ReservationController;
use App\Controllers\AdminScheduleController;
use App\Middlewares\AdminMiddleware;
use App\Controllers\Registration\RegisterController;
use App\Controllers\Registration\ShowRegistrationController;
use App\Controllers\Registration\VerifyEmailController;
use Velo\Middlewares\AntiCsrf\AntiCsrfMiddleware;
use Velo\Middlewares\AuthMiddleware\WebAuthMiddleware;
use Velo\Router\Router\Router;

/**
 * @var Router $router
 */

$router->get('/', HomeController::class);
$router->get('/register', ShowRegistrationController::class);
$router->post('/register', RegisterController::class)
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->get('/verify-email/{token}', VerifyEmailController::class);
$router->get('/reservation', ReservationController::class);
$router->get('/reservation/slots', ReservationController::class, 'slots');
$router->get('/reservation/tables', ReservationController::class, 'tables');
$router->post('/reservation', ReservationController::class, 'store')
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->get('/admin/schedule', AdminScheduleController::class)
    ->addMiddleware(WebAuthMiddleware::class)
    ->addMiddleware(AdminMiddleware::class);
$router->post('/admin/schedule/blocks', AdminScheduleController::class, 'store')
    ->addMiddleware(WebAuthMiddleware::class)
    ->addMiddleware(AdminMiddleware::class)
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->post('/admin/schedule/blocks/{id}/update', AdminScheduleController::class, 'update')
    ->addMiddleware(WebAuthMiddleware::class)
    ->addMiddleware(AdminMiddleware::class)
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->post('/admin/schedule/blocks/{id}/delete', AdminScheduleController::class, 'delete')
    ->addMiddleware(WebAuthMiddleware::class)
    ->addMiddleware(AdminMiddleware::class)
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->get('/login', ShowAuthController::class, 'login');
$router->post('/login', ActionAuthController::class, 'login')
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->get('/forgot-password', ShowAuthController::class, 'forgotPassword');
$router->post('/forgot-password', ActionAuthController::class, 'requestPasswordReset')
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->get('/reset-password/{token}', ShowAuthController::class, 'resetPassword');
$router->post('/reset-password/{token}', ActionAuthController::class, 'resetPassword')
    ->addMiddleware(AntiCsrfMiddleware::class);
$router->post('/logout', ActionAuthController::class, 'logout')
    ->addMiddleware(AntiCsrfMiddleware::class);
