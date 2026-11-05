<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends TestCase
{
    #[Test]
    public function it_returns_verified_user_by_email(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['email' => 'user@example.test']);
        $statement->method('fetch')->willReturn([
            'account_id' => 7,
            'password_hash' => 'hash',
        ]);

        $connection = $this->createMock(PDO::class);
        $connection->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('verfication_token_used_at IS NOT NULL'))
            ->willReturn($statement);

        $repository = new UserRepository($connection);
        $user = $repository->findVerifiedByEmail('user@example.test');

        self::assertNotNull($user);
        self::assertSame(7, $user->id());
        self::assertSame('hash', $user->passwordHash());
    }

    #[Test]
    public function it_returns_null_when_user_does_not_exist(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('fetch')->willReturn(false);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        $repository = new UserRepository($connection);

        self::assertNull($repository->findVerifiedByEmail('missing@example.test'));
    }

    #[Test]
    public function it_returns_verified_account_id_by_email(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['email' => 'user@example.test']);
        $statement->method('fetchColumn')->willReturn(7);

        $connection = $this->createMock(PDO::class);
        $connection->expects(self::once())
            ->method('prepare')
            ->willReturn($statement);

        $repository = new UserRepository($connection);

        self::assertSame(7, $repository->findVerifiedAccountIdByEmail('user@example.test'));
    }
}
