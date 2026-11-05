<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepositoryInterface;
use App\Services\Registration\EmailVerificationService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailVerificationService::class)]
final class EmailVerificationServiceTest extends TestCase
{
    #[Test]
    public function it_rejects_token_with_invalid_format_without_querying_database(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects(self::never())->method('verifyEmail');

        $service = new EmailVerificationService($repository);

        self::assertFalse($service->verify('invalid-token'));
    }

    #[Test]
    public function it_returns_true_when_one_verification_record_is_updated(): void
    {
        $token = str_repeat('a', 64);
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('verifyEmail')
            ->with($token)
            ->willReturn(true);

        $service = new EmailVerificationService($repository);

        self::assertTrue($service->verify($token));
    }

    #[Test]
    public function it_returns_false_when_no_verification_record_is_updated(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('verifyEmail')->willReturn(false);

        $service = new EmailVerificationService($repository);

        self::assertFalse($service->verify(str_repeat('f', 64)));
    }
}
