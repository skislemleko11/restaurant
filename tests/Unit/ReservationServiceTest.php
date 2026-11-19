<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\ReservationRepositoryInterface;
use App\Services\ReservationService;
use DateMalformedStringException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReservationService::class)]
final class ReservationServiceTest extends TestCase
{
    #[Test]
    public function it_rejects_invalid_party_size(): void
    {
        $service = new ReservationService($this->createStub(ReservationRepositoryInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Podaj liczbę osób od 1 do 12.');

        $service->getSlots('2026-12-01', 0);
    }

    #[Test]
    public function it_rejects_party_larger_than_limit(): void
    {
        $service = new ReservationService($this->createStub(ReservationRepositoryInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maksymalna liczba osób w rezerwacji to 12.');

        $service->getSlots('2026-12-01', 13);
    }

    #[Test]
    public function it_returns_blocked_tables_without_availability(): void
    {
        $repository = $this->createStub(ReservationRepositoryInterface::class);
        $repository->method('hasBlockedPeriodConflict')->willReturn(true);
        $service = new ReservationService($repository);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ten termin jest zablokowany.');

        $service->getTables('2026-12-01', '12:00', 2);
    }

    #[Test]
    public function it_maps_available_tables(): void
    {
        $repository = $this->createStub(ReservationRepositoryInterface::class);
        $repository->method('hasBlockedPeriodConflict')->willReturn(false);
        $repository->method('findTables')->willReturn([
            ['table_id' => 1, 'name' => 'Stolik 1', 'location' => 'Sala', 'capacity' => 4, 'is_reserved' => 0],
            ['table_id' => 2, 'name' => 'Stolik 2', 'location' => 'Taras', 'capacity' => 2, 'is_reserved' => 1],
        ]);
        $service = new ReservationService($repository);

        $tables = $service->getTables(
            (new \DateTimeImmutable('tomorrow', new \DateTimeZone(ReservationService::TIMEZONE)))
                ->format('Y-m-d'),
            '12:00',
            2
        );

        self::assertCount(2, $tables);
        self::assertTrue($tables[0]['is_available']);
        self::assertTrue($tables[0]['fits_party']);
        self::assertFalse($tables[1]['is_available']);
    }

    #[Test]
    public function it_rolls_back_and_rethrows_conflict(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->method('findCapacityForUpdate')->willReturn(4);
        $repository->method('hasBlockedPeriodConflict')->willReturn(false);
        $repository->method('hasConflict')->willReturn(true);
        $repository->expects(self::once())->method('beginTransaction');
        $repository->expects(self::once())->method('rollBack');
        $repository->expects(self::never())->method('commit');
        $repository->expects(self::never())->method('create');

        $service = new ReservationService($repository);
        $date = (new \DateTimeImmutable('tomorrow', new \DateTimeZone(ReservationService::TIMEZONE)))
            ->format('Y-m-d');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ten stolik został już zarezerwowany.');

        $service->createReservation(7, $date, '12:00', 1, 2);
    }

    #[Test]
    public function it_commits_successful_reservation(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->method('findCapacityForUpdate')->willReturn(4);
        $repository->method('hasBlockedPeriodConflict')->willReturn(false);
        $repository->method('hasConflict')->willReturn(false);
        $repository->method('sumReservedPeople')->willReturn(2);
        $repository->method('sumActiveTableCapacity')->willReturn(20);
        $repository->expects(self::once())->method('beginTransaction');
        $repository->expects(self::once())->method('commit');
        $repository->expects(self::never())->method('rollBack');
        $repository->expects(self::once())
            ->method('create')
            ->with(7, 1, self::isInstanceOf(\DateTimeImmutable::class), 2);

        $service = new ReservationService($repository);
        $date = (new \DateTimeImmutable('tomorrow', new \DateTimeZone(ReservationService::TIMEZONE)))
            ->format('Y-m-d');

        $service->createReservation(7, $date, '12:00', 1, 2);
    }

    #[Test]
    #[DataProvider('invalidSlotProvider')]
    public function it_rejects_invalid_or_unavailable_slots(string $date, string $time): void
    {
        $service = new ReservationService($this->createStub(ReservationRepositoryInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $service->createReservation(7, $date, $time, 1, 2);
    }

    public static function invalidSlotProvider(): array
    {
        return [
            'past date' => ['2020-01-01', '12:00'],
            'outside hours' => ['2030-12-01', '11:00'],
            'invalid time format' => ['2030-12-01', '25:99'],
        ];
    }
}
