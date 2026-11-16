<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepositoryInterface;
use App\Repositories\PasswordResetRepositoryInterface;
use App\Services\Auth\PasswordResetMailerInterface;
use App\Services\Auth\PasswordResetException;
use App\Services\Auth\PasswordResetService;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PasswordResetService::class)]
final class PasswordResetServiceTest extends TestCase
{
    #[Test]
    public function it_does_not_send_mail_for_unknown_account(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findVerifiedAccountIdByEmail')
            ->with('missing@example.test')
            ->willReturn(null);

        $mailer = $this->createMock(PasswordResetMailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $connection = $this->createMock(PDO::class);
        $connection->expects(self::never())->method('beginTransaction');

        $service = new PasswordResetService(
            $connection,
            $repository,
            $this->createMock(PasswordResetRepositoryInterface::class),
            $mailer
        );

        $service->requestReset('missing@example.test');
    }

    #[Test]
    public function it_rejects_invalid_reset_token_without_querying_database(): void
    {
        $connection = $this->createMock(PDO::class);
        $connection->expects(self::never())->method('beginTransaction');

        $this->expectException(PasswordResetException::class);
        $this->expectExceptionMessageIs('Link do resetowania hasła jest nieprawidłowy lub wygasł.');

        $service = new PasswordResetService(
            $connection,
            $this->createMock(UserRepositoryInterface::class),
            $this->createMock(PasswordResetRepositoryInterface::class),
            $this->createMock(PasswordResetMailerInterface::class)
        );

        $service->reset('invalid-token', 'new-password');
    }

    #[Test]
    public function it_stores_token_and_sends_reset_email_for_verified_account(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('findVerifiedAccountIdByEmail')->willReturn(7);

        $connection = $this->createMock(PDO::class);
        $connection->expects(self::once())->method('beginTransaction')->willReturn(true);
        $connection->expects(self::once())->method('commit');
        $resetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
        $resetRepository->expects(self::once())
            ->method('save')
            ->with(
                7,
                self::matchesRegularExpression('/^[a-f0-9]{64}$/'),
                self::callback(static fn ($value): bool => is_string($value))
            );

        $mailer = $this->createMock(PasswordResetMailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with('user@example.test', self::matchesRegularExpression('/^[a-f0-9]{64}$/'));

        $service = new PasswordResetService($connection, $repository, $resetRepository, $mailer);
        $service->requestReset('user@example.test');
    }

    #[Test]
    public function it_changes_password_and_invalidates_used_token(): void
    {
        $token = str_repeat('a', 64);
        $connection = $this->createMock(PDO::class);
        $connection->expects(self::once())->method('beginTransaction')->willReturn(true);
        $connection->expects(self::once())->method('commit');
        $resetRepository = $this->createMock(PasswordResetRepositoryInterface::class);
        $resetRepository->expects(self::once())
            ->method('findAccountIdByValidToken')
            ->with($token)
            ->willReturn(7);
        $resetRepository->expects(self::once())
            ->method('deleteByAccountId')
            ->with(7);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('updatePassword')
            ->with(7, self::callback(
                static fn (string $hash): bool => password_verify('new-password', $hash)
            ));

        $service = new PasswordResetService(
            $connection,
            $userRepository,
            $resetRepository,
            $this->createMock(PasswordResetMailerInterface::class)
        );

        $service->reset($token, 'new-password');
    }
}
