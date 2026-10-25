<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Registration\EmailVerificationService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailVerificationService::class)]
final class EmailVerificationServiceTest extends TestCase
{
    #[Test]
    public function it_rejects_token_with_invalid_format_without_querying_database(): void
    {
        $connection = $this->createMock(PDO::class);
        $connection->expects(self::never())->method('prepare');

        self::assertFalse(new EmailVerificationService($connection)->verify('invalid-token'));
    }

    #[Test]
    public function it_returns_true_when_one_verification_record_is_updated(): void
    {
        $token = str_repeat('a', 64);
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with(['token' => $token]);
        $statement->expects(self::once())->method('rowCount')->willReturn(1);

        $connection = $this->createMock(PDO::class);
        $connection->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('UPDATE users'))
            ->willReturn($statement);

        self::assertTrue(new EmailVerificationService($connection)->verify($token));
    }

    #[Test]
    public function it_returns_false_when_no_verification_record_is_updated(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->method('rowCount')->willReturn(0);

        $connection = $this->createMock(PDO::class);
        $connection->method('prepare')->willReturn($statement);

        self::assertFalse(
            new EmailVerificationService($connection)
                ->verify(str_repeat('f', 64))
        );
    }
}
