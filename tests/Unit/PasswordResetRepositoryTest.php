<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\PasswordResetRepository;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PasswordResetRepository::class)]
final class PasswordResetRepositoryTest extends TestCase
{
    #[Test]
    public function it_saves_reset_token(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                'account_id' => 7,
                'token' => str_repeat('a', 64),
                'expires_at' => '2026-12-01 12:00:00',
            ]);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        (new PasswordResetRepository($connection))->save(
            7,
            str_repeat('a', 64),
            '2026-12-01 12:00:00'
        );
    }

    #[Test]
    public function it_returns_account_id_for_valid_token(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('execute');
        $statement->method('fetchColumn')->willReturn(7);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        self::assertSame(
            7,
            (new PasswordResetRepository($connection))->findAccountIdByValidToken(str_repeat('a', 64))
        );
    }

    #[Test]
    public function it_returns_null_for_missing_or_expired_token(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('fetchColumn')->willReturn(false);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        self::assertNull(
            (new PasswordResetRepository($connection))->findAccountIdByValidToken(str_repeat('b', 64))
        );
    }

    #[Test]
    public function it_deletes_reset_rows_by_account_id(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('execute')->with(['account_id' => 7]);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        (new PasswordResetRepository($connection))->deleteByAccountId(7);
    }
}
