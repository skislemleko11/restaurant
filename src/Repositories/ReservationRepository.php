<?php
declare(strict_types=1);

namespace App\Repositories;

use DateTimeImmutable;
use PDO;

final readonly class ReservationRepository implements ReservationRepositoryInterface
{
    public function __construct(private PDO $connection)
    {
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    public function countAvailableTables(int $people, string $windowStart, string $windowEnd): int
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*)
             FROM tables_info t
             WHERE t.capacity >= :people
               AND NOT EXISTS (
                   SELECT 1
                   FROM reservation r
                   WHERE r.table_id = t.table_id
                     AND r.date_of_reservation > :window_start
                     AND r.date_of_reservation < :window_end
               )'
        );
        $statement->execute([
            'people' => $people,
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
        ]);

        return (int)$statement->fetchColumn();
    }

    public function findTables(string $windowStart, string $windowEnd): array
    {
        $statement = $this->connection->prepare(
            'SELECT t.table_id, t.location, t.capacity,
                    EXISTS (
                        SELECT 1
                        FROM reservation r
                        WHERE r.table_id = t.table_id
                          AND r.date_of_reservation > :window_start
                          AND r.date_of_reservation < :window_end
                    ) AS is_reserved
             FROM tables_info t
             ORDER BY t.table_id'
        );
        $statement->execute([
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
        ]);

        return $statement->fetchAll();
    }

    public function findCapacityForUpdate(int $tableId): ?int
    {
        $statement = $this->connection->prepare(
            'SELECT capacity
             FROM tables_info
             WHERE table_id = :table_id
             FOR UPDATE'
        );
        $statement->execute(['table_id' => $tableId]);
        $capacity = $statement->fetchColumn();

        return $capacity === false ? null : (int)$capacity;
    }

    public function hasConflict(int $tableId, string $windowStart, string $windowEnd): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1
             FROM reservation
             WHERE table_id = :table_id
               AND date_of_reservation > :window_start
               AND date_of_reservation < :window_end
             LIMIT 1'
        );
        $statement->execute([
            'table_id' => $tableId,
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function create(int $accountId, int $tableId, DateTimeImmutable $slot): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO reservation (account_id, table_id, date_of_reservation)
             VALUES (:account_id, :table_id, :date_of_reservation)'
        );
        $statement->execute([
            'account_id' => $accountId,
            'table_id' => $tableId,
            'date_of_reservation' => $slot->format('Y-m-d H:i:s'),
        ]);
    }
}
