<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\EnvUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(EnvUtils::class)]
final class EnvUtilsTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['TEST_REQUIRED_VALUE']);
    }

    #[Test]
    public function it_returns_trimmed_enviroment_value(): void
    {
        $_ENV['TEST_REQUIRED_VALUE'] = '  value  ';

        self::assertSame('value', EnvUtils::getRequiredEnvValue('TEST_REQUIRED_VALUE'));
    }

    #[Test]
    public function it_throws_when_environment_value_is_missing(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageIs('Required environment variable not set: TEST_REQUIRED_VALUE');

        EnvUtils::getRequiredEnvValue('TEST_REQUIRED_VALUE');
    }
}
