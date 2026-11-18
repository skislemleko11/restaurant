<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\ReservationRepositoryInterface;
use App\Services\ReservationService;
use PHPUnit\Framework\TestCase;

final class ReservationBlockedPeriodTest extends TestCase
{
    public function testBlockedWindowStartsAtReservationSlot(): void
    {
        $repository = $this->createStub(ReservationRepositoryInterface::class);
        $service = new ReservationService($repository);
        $method = new \ReflectionMethod(ReservationService::class, 'blockedWindow');
        $slot = new \DateTimeImmutable(
            '2026-11-18 15:30:00',
            new \DateTimeZone(ReservationService::TIMEZONE)
        );

        self::assertSame(
            ['2026-11-18 15:30:00', '2026-11-18 17:00:00'],
            $method->invoke($service, $slot)
        );
    }

    public function testReservationCanStartWhenBlockAlreadyEnded(): void
    {
        $repository = $this->createStub(ReservationRepositoryInterface::class);
        $repository->method('hasBlockedPeriodConflict')->willReturnCallback(
            static fn(string $start, string $end): bool =>
                !($end <= '2026-11-18 14:00:00' || $start >= '2026-11-18 14:00:00')
        );

        $service = new ReservationService($repository);
        $method = new \ReflectionMethod(ReservationService::class, 'blockedWindow');
        $slot = new \DateTimeImmutable(
            '2026-11-18 14:30:00',
            new \DateTimeZone(ReservationService::TIMEZONE)
        );
        [$blockedStart, $blockedEnd] = $method->invoke($service, $slot);

        self::assertFalse(
            $repository->hasBlockedPeriodConflict($blockedStart, $blockedEnd)
        );
    }
}
