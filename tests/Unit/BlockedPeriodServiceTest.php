<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\ReservationRepositoryInterface;
use App\Services\BlockedPeriodService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BlockedPeriodService::class)]
final class BlockedPeriodServiceTest extends TestCase
{
    #[Test]
    public function it_maps_calendar_entries(): void
    {
        $repository = $this->createStub(ReservationRepositoryInterface::class);
        $repository->method('findBlockedPeriods')->willReturn([
            ['id' => 3, 'start' => '2026-12-01 12:00:00', 'end' => '2026-12-01 14:00:00', 'reason' => 'Remont'],
        ]);

        $calendar = (new BlockedPeriodService($repository))->getCalendar();

        self::assertSame([
            ['id' => 3, 'start' => '2026-12-01 12:00:00', 'end' => '2026-12-01 14:00:00', 'reason' => 'Remont'],
        ], $calendar);
    }

    #[Test]
    public function it_creates_all_day_block(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('createBlockedPeriod')
            ->with('2026-12-01 00:00:00', '2026-12-01 23:59:59', 'Remont');

        (new BlockedPeriodService($repository))->create(
            '2026-12-01',
            '2026-12-01',
            null,
            null,
            ' Remont '
        );
    }

    #[Test]
    public function it_rejects_end_before_start(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data końcowa');

        (new BlockedPeriodService($this->createStub(ReservationRepositoryInterface::class)))
            ->create('2026-12-02', '2026-12-01', null, null, null);
    }

    #[Test]
    public function it_rejects_end_time_not_after_start(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Koniec blokady');

        (new BlockedPeriodService($this->createStub(ReservationRepositoryInterface::class)))
            ->create('2026-12-01', '2026-12-01', '14:00', '14:00', null);
    }

    #[Test]
    public function it_updates_existing_block(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('updateBlockedPeriod')
            ->with(5, '2026-12-01 12:00:00', '2026-12-01 14:00:00', null)
            ->willReturn(true);

        (new BlockedPeriodService($repository))->update(
            5,
            '2026-12-01',
            '2026-12-01',
            '12:00',
            '14:00',
            null
        );
    }

    #[Test]
    public function it_rejects_unknown_block_update(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->method('updateBlockedPeriod')->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Wybrana blokada nie istnieje.');

        (new BlockedPeriodService($repository))->update(
            99,
            '2026-12-01',
            '2026-12-01',
            '12:00',
            '14:00',
            null
        );
    }

    #[Test]
    public function it_deletes_existing_block(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->expects(self::once())->method('deleteBlockedPeriod')->with(4)->willReturn(true);

        (new BlockedPeriodService($repository))->delete(4);
    }
}
