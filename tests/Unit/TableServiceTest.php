<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\ReservationRepositoryInterface;
use App\Services\TableService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TableService::class)]
final class TableServiceTest extends TestCase
{
    #[Test]
    public function it_maps_table_data(): void
    {
        $repository = $this->createStub(ReservationRepositoryInterface::class);
        $repository->method('findAllTables')->willReturn([
            ['table_id' => 3, 'name' => 'Stolik 3', 'location' => 'Sala', 'capacity' => 4, 'is_active' => 1],
        ]);

        self::assertSame([
            ['id' => 3, 'name' => 'Stolik 3', 'location' => 'Sala', 'capacity' => 4, 'is_active' => true],
        ], (new TableService($repository))->getTables());
    }

    #[Test]
    public function it_creates_active_table(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('createTable')
            ->with('Stolik 11', 'Taras', 6, true);

        (new TableService($repository))->create(' Stolik 11 ', 'Taras', '6', '1');
    }

    #[Test]
    public function it_rejects_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Podaj unikalny numer lub nazwę stolika.');

        (new TableService($this->createStub(ReservationRepositoryInterface::class)))
            ->create(' ', 'Sala', 4, true);
    }

    #[Test]
    public function it_rejects_capacity_outside_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TableService($this->createStub(ReservationRepositoryInterface::class)))
            ->create('Stolik 1', 'Sala', 13, true);
    }

    #[Test]
    public function it_updates_existing_table(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('updateTable')
            ->with(3, 'Stolik 3', 'Taras', 8, false)
            ->willReturn(true);

        (new TableService($repository))->update(3, 'Stolik 3', 'Taras', 8, null);
    }

    #[Test]
    public function it_rejects_update_for_unknown_table(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->method('updateTable')->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Wybrany stolik nie istnieje.');

        (new TableService($repository))->update(99, 'Stolik 99', 'Sala', 4, true);
    }

    #[Test]
    public function it_refuses_delete_when_table_has_reservations(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->method('tableHasReservations')->with(1)->willReturn(true);
        $repository->expects(self::never())->method('deleteTable');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('nie można usunąć');

        (new TableService($repository))->delete(1);
    }

    #[Test]
    public function it_deletes_unused_table(): void
    {
        $repository = $this->createMock(ReservationRepositoryInterface::class);
        $repository->method('tableHasReservations')->willReturn(false);
        $repository->expects(self::once())->method('deleteTable')->with(1)->willReturn(true);

        (new TableService($repository))->delete(1);
    }
}
