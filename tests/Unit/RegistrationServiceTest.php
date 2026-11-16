<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Mailer;
use App\Repositories\UserRepositoryInterface;
use App\Services\Registration\EmailVerificationMailer;
use App\Services\Registration\RegistrationException;
use App\Services\Registration\RegistrationService;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(RegistrationService::class)]
final class RegistrationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS'] as $key) {
            unset($_ENV[$key]);
        }
    }

    #[Test]
    public function it_wraps_unexpected_failure_and_rolls_back_active_transaction(): void
    {
        $connection = $this->createMock(PDO::class);

        $connection->expects(self::once())
            ->method('beginTransaction')
            ->willReturn(true);

        $connection->expects(self::once())
            ->method('inTransaction')->willReturn(true);

        $connection->expects(self::once())
            ->method('rollBack');

        $mailer = $this->createMailer();
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $service = new RegistrationService(
            $connection,
            $userRepository,
            new EmailVerificationMailer($mailer)
        );

        $this->expectException(RegistrationException::class);
        $this->expectExceptionMessageIs(
            'Konto nie zostało utworzone, ponieważ nie udało się wysłać maila weryfikacyjnego.'
        );
        $this->expectExceptionCode(0);

        $userRepository->expects(self::once())
            ->method('create')
            ->willThrowException(new RuntimeException('database failure'));

        $service->register('user@example.test', 'password');
    }

    private function createMailer(): Mailer
    {
        $_ENV['MAIL_HOST'] = 'smtp.example.test';
        $_ENV['MAIL_USERNAME'] = 'user';
        $_ENV['MAIL_PASSWORD'] = 'password';
        $_ENV['MAIL_FROM_ADDRESS'] = 'from@example.test';

        return new Mailer();
    }
}
