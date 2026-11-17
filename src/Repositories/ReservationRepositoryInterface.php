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
     * @return list<array{table_id: int|string, location: string, capacity: int|string, is_reserved: int|string|bool}>
     */
    public function findTables(string $windowStart, string $windowEnd): array;

    public function findCapacityForUpdate(int $tableId): ?int;

    public function hasConflict(int $tableId, string $windowStart, string $windowEnd): bool;

    public function create(int $accountId, int $tableId, DateTimeImmutable $slot): void;

    /**
     * @return list<array{id: int|string, start: string, end: string, reason: string|null}>
     */
    public function findBlockedPeriods(string $from, string $to): array;

    public function hasBlockedPeriodConflict(string $windowStart, string $windowEnd): bool;

    public function createBlockedPeriod(string $start, string $end, ?string $reason): void;

    public function updateBlockedPeriod(int $id, string $start, string $end, ?string $reason): bool;

    public function deleteBlockedPeriod(int $id): bool;
}
