<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;

interface ReservationRepositoryInterface
{
    public function beginTransaction(): void;

    public function commit(): void;

    public function rollBack(): void;

    public function countAvailableTables(int $people, string $windowStart, string $windowEnd): int;

    /**
     * @return list<array{table_id: int|string, name: string, location: string, capacity: int|string, is_reserved: int|string|bool}>
     */
    public function findTables(string $windowStart, string $windowEnd): array;

    public function findCapacityForUpdate(int $tableId): ?int;

    public function sumReservedPeople(string $windowStart, string $windowEnd): int;

    public function sumActiveTableCapacity(): int;

    public function hasConflict(int $tableId, string $windowStart, string $windowEnd): bool;

    public function create(int $accountId, int $tableId, DateTimeImmutable $slot, int $numberOfPeople): void;

    /**
     * @return list<array{reservation_id: int|string, date_of_reservation: string, number_of_people: int|string, table_name: string, location: string, email: string}>
     */
    public function findAllReservations(): array;

    /**
     * @return list<array{reservation_id: int|string, date_of_reservation: string, number_of_people: int|string, table_name: string, location: string}>
     */
    public function findReservationsByAccount(int $accountId): array;

    public function deleteReservation(int $reservationId, int $accountId): bool;

    /**
     * @return list<array{id: int|string, start: string, end: string, reason: string|null}>
     */
    public function findBlockedPeriods(string $from, string $to): array;

    public function hasBlockedPeriodConflict(string $windowStart, string $windowEnd): bool;

    public function createBlockedPeriod(string $start, string $end, ?string $reason): void;

    public function updateBlockedPeriod(int $id, string $start, string $end, ?string $reason): bool;

    public function deleteBlockedPeriod(int $id): bool;

    /**
     * @return list<array{table_id: int|string, name: string, location: string, capacity: int|string, is_active: int|string|bool}>
     */
    public function findAllTables(): array;

    public function createTable(string $name, string $location, int $capacity, bool $isActive): void;

    public function updateTable(int $tableId, string $name, string $location, int $capacity, bool $isActive): bool;

    public function tableHasReservations(int $tableId): bool;

    public function deleteTable(int $tableId): bool;
}
