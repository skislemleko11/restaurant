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
}
