<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Exceptions\ForbiddenException;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use Velo\Http\Request;
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

    /**
     * @throws ForbiddenException
     */
    public function handle(Request $request, callable $next): Response
    {
        $userId = $this->session->get(AuthService::USER_ID_SESSION_KEY);
        if (!is_int($userId) && !is_string($userId)) {
            throw new ForbiddenException();
        }

        $accountId = filter_var($userId, FILTER_VALIDATE_INT);
        if ($accountId === false || !$this->userRepository->isAdmin($accountId)) {
            throw new ForbiddenException();
        }

        return $next($request);
    }
}
