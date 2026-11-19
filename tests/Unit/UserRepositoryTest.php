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

    #[Test]
    public function it_creates_user_with_expected_parameters(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                'email' => 'user@example.test',
                'password_hash' => 'hash',
                'verification_token' => str_repeat('a', 64),
                'expires_at' => '2026-12-01 12:00:00',
            ]);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        (new \App\Repositories\UserRepository($connection))->create(
            'user@example.test',
            'hash',
            str_repeat('a', 64),
            '2026-12-01 12:00:00'
        );
    }

    #[Test]
    public function it_verifies_email_only_for_one_updated_row(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->method('rowCount')->willReturn(1);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        self::assertTrue(
            (new \App\Repositories\UserRepository($connection))->verifyEmail(str_repeat('a', 64))
        );
    }

    #[Test]
    public function it_updates_password(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['password_hash' => 'new-hash', 'account_id' => 7]);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        (new \App\Repositories\UserRepository($connection))->updatePassword(7, 'new-hash');
    }

    #[Test]
    public function it_checks_admin_role(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['account_id' => 7, 'role' => 'admin']);
        $statement->method('fetchColumn')->willReturn(1);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        self::assertTrue((new \App\Repositories\UserRepository($connection))->isAdmin(7));
    }
}
