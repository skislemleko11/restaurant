<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Entities\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthService::class)]
final class AuthServiceTest extends TestCase
{
    #[Test]
    public function it_returns_user_id_for_valid_credentials(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findVerifiedByEmail')
            ->with('user@example.test')
            ->willReturn(new User(7, password_hash('correct-password', PASSWORD_ARGON2ID)));

        $service = new AuthService($repository);

        self::assertSame(7, $service->authenticate('user@example.test', 'correct-password'));
    }

    #[Test]
    public function it_returns_null_for_invalid_credentials(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('findVerifiedByEmail')
            ->willReturn(new User(7, password_hash('correct-password', PASSWORD_ARGON2ID)));

        $service = new AuthService($repository);

        self::assertNull($service->authenticate('user@example.test', 'wrong-password'));
    }
}
