<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use Velo\Http\Request;
use Velo\Http\Responses\Concrete\TextResponse;
use Velo\Http\Responses\Response;
use Velo\Router\Middlewares\MiddlewareInterface;
use Velo\Session\Session\SessionInterface;

final readonly class AdminMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionInterface $session,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $userId = $this->session->get(AuthService::USER_ID_SESSION_KEY);
        if (!is_int($userId) && !is_string($userId)) {
            return new TextResponse('Brak uprawnień.', 403);
        }
        $accountId = filter_var($userId, FILTER_VALIDATE_INT);
        if ($accountId === false || !$this->userRepository->isAdmin($accountId)) {
            return new TextResponse('Brak uprawnień.', 403);
        }

        return $next($request);
    }
}
