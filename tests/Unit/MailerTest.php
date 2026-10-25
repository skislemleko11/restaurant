<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Mailer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(Mailer::class)]
final class MailerTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'] as $key) {
            unset($_ENV[$key]);
        }
    }

    #[Test]
    public function it_requires_mail_host_configuration(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIs('Required environment variable not set: MAIL_HOST');

        new Mailer();
    }

    #[Test]
    public function it_delegates_mailer_configuration_and_methods(): void
    {
        $_ENV['MAIL_HOST'] = 'smtp.example.test';
        $_ENV['MAIL_USERNAME'] = 'user';
        $_ENV['MAIL_PASSWORD'] = 'password';
        $_ENV['MAIL_FROM_ADDRESS'] = 'from@example.test';
        $_ENV['MAIL_FROM_NAME'] = 'Test';

        $mailer = new Mailer();
        $mailer->Subject = 'Subject';
        $mailer->addAddress('recipient@example.test');

        self::assertSame('Subject', $mailer->Subject);
        self::assertSame(
            [['recipient@example.test', '']],
            $mailer->getToAddresses()
        );
    }
}
