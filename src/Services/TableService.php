<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReservationRepositoryInterface;
use InvalidArgumentException;
use PDOException;

final readonly class TableService
{
    public function __construct(private ReservationRepositoryInterface $reservationRepository)
    {
    }

    /**
     * @return list<array{id: int, name: string, location: string, capacity: int, is_active: bool}>
     */
    public function getTables(): array
    {
        return array_map(
            static fn(array $table): array => [
                'id' => (int)$table['table_id'],
                'name' => (string)$table['name'],
                'location' => (string)$table['location'],
                'capacity' => (int)$table['capacity'],
                'is_active' => (bool)(int)$table['is_active'],
            ],
            $this->reservationRepository->findAllTables()
        );
    }

    public function create(mixed $name, mixed $location, mixed $capacity, mixed $active): void
    {
        [$cleanName, $cleanLocation, $cleanCapacity, $isActive] =
            $this->validate($name, $location, $capacity, $active);
        try {
            $this->reservationRepository->createTable($cleanName, $cleanLocation, $cleanCapacity, $isActive);
        } catch (PDOException $exception) {
            $this->throwForDatabaseError($exception, 'Nie można dodać stolika.');
        }
    }

    public function update(
        mixed $rawId,
        mixed $name,
        mixed $location,
        mixed $capacity,
        mixed $active
    ): void {
        $id = filter_var($rawId, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw new InvalidArgumentException('Wybrany stolik jest nieprawidłowy.');
        }
        [$cleanName, $cleanLocation, $cleanCapacity, $isActive] =
            $this->validate($name, $location, $capacity, $active);
        try {
            if (!$this->reservationRepository->updateTable($id, $cleanName, $cleanLocation, $cleanCapacity, $isActive)) {
                throw new InvalidArgumentException('Wybrany stolik nie istnieje.');
            }
        } catch (PDOException $exception) {
            $this->throwForDatabaseError($exception, 'Nie można zmienić stolika.');
        }
    }

    public function delete(mixed $rawId): void
    {
        $id = filter_var($rawId, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw new InvalidArgumentException('Wybrany stolik jest nieprawidłowy.');
        }
        if ($this->reservationRepository->tableHasReservations($id)) {
            throw new InvalidArgumentException('Stolika z historią rezerwacji nie można usunąć. Można go dezaktywować.');
        }
        if (!$this->reservationRepository->deleteTable($id)) {
            throw new InvalidArgumentException('Wybrany stolik nie istnieje.');
        }
    }

    /**
     * @return array{0: string, 1: string, 2: int, 3: bool}
     */
    private function validate(mixed $name, mixed $location, mixed $capacity, mixed $active): array
    {
        if (!is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException('Podaj unikalny numer lub nazwę stolika.');
        }
        $cleanName = trim($name);
        if (mb_strlen($cleanName) > 255) {
            throw new InvalidArgumentException('Nazwa stolika może mieć maksymalnie 255 znaków.');
        }

        $cleanLocation = is_string($location) ? trim($location) : '';
        if (mb_strlen($cleanLocation) > 255) {
            throw new InvalidArgumentException('Nazwa miejsca może mieć maksymalnie 255 znaków.');
        }

        $capacityNumber = filter_var($capacity, FILTER_VALIDATE_INT);
        if ($capacityNumber === false || $capacityNumber < 1 || $capacityNumber > ReservationService::MAX_PARTY_SIZE) {
            throw new InvalidArgumentException(
                'Pojemność stolika musi być liczbą od 1 do ' . ReservationService::MAX_PARTY_SIZE . '.'
            );
        }

        $isActive = $active === null || $active === false || $active === 0
            || $active === '0' || $active === 'off' || $active === 'false'
            ? false
            : true;

        return [$cleanName, $cleanLocation, $capacityNumber, $isActive];
    }

    private function throwForDatabaseError(PDOException $exception, string $fallback): never
    {
        if ($exception->getCode() === '23000') {
            throw new InvalidArgumentException('Stolik o tej nazwie już istnieje.');
        }
        throw new InvalidArgumentException($fallback);
    }
}
