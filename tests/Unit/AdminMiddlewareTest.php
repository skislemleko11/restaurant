<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ForbiddenException;
use App\Middlewares\AdminMiddleware;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Velo\Http\Request;
use Velo\Http\RequestMethod;
use Velo\Session\Session\SessionInterface;

#[CoversClass(AdminMiddleware::class)]
final class AdminMiddlewareTest extends TestCase
{
    #[Test]
    public function it_rejects_request_without_user_in_session(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with(AuthService::USER_ID_SESSION_KEY)->willReturn(null);

        $middleware = new AdminMiddleware(
            $session,
            $this->createStub(UserRepositoryInterface::class)
        );

        $this->expectException(ForbiddenException::class);
        $middleware->handle(
            new Request('/admin/menu', RequestMethod::GET),
            static fn(Request $request) => throw new \LogicException('next should not run')
        );
    }

    #[Test]
    public function it_rejects_non_admin_user(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with(AuthService::USER_ID_SESSION_KEY)->willReturn(7);

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('isAdmin')->with(7)->willReturn(false);

        $middleware = new AdminMiddleware($session, $repository);

        $this->expectException(ForbiddenException::class);
        $middleware->handle(
            new Request('/admin/menu', RequestMethod::GET),
            static fn(Request $request) => throw new \LogicException('next should not run')
        );
    }

    #[Test]
    public function it_calls_next_for_admin_user(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with(AuthService::USER_ID_SESSION_KEY)->willReturn('7');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('isAdmin')->with(7)->willReturn(true);

        $middleware = new AdminMiddleware($session, $repository);
        $expectedRequest = new Request('/admin/menu', RequestMethod::GET);

        $response = $middleware->handle(
            $expectedRequest,
            static fn(Request $request) => new \Velo\Http\Responses\Concrete\TextResponse('ok')
        );

        self::assertSame(200, $response->statusCode);
    }
}
