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
        if ($this->hasBlockedPeriodConflict($windowStart, $windowEnd)) {
            return 0;
        }

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

    public function hasBlockedPeriodConflict(string $windowStart, string $windowEnd): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1
             FROM reservation_block
             WHERE starts_at < :window_end
               AND ends_at > :window_start
             LIMIT 1'
        );
        $statement->execute([
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

    public function findBlockedPeriods(string $from, string $to): array
    {
        $statement = $this->connection->prepare(
            'SELECT block_id AS id, starts_at AS start, ends_at AS end, reason
             FROM reservation_block
             WHERE starts_at < :to_date
               AND ends_at > :from_date
             ORDER BY starts_at'
        );
        $statement->execute(['from_date' => $from, 'to_date' => $to]);

        return $statement->fetchAll();
    }

    public function createBlockedPeriod(string $start, string $end, ?string $reason): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO reservation_block (starts_at, ends_at, reason)
             VALUES (:starts_at, :ends_at, :reason)'
        );
        $statement->execute([
            'starts_at' => $start,
            'ends_at' => $end,
            'reason' => $reason,
        ]);
    }

    public function updateBlockedPeriod(int $id, string $start, string $end, ?string $reason): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE reservation_block
             SET starts_at = :starts_at, ends_at = :ends_at, reason = :reason
             WHERE block_id = :block_id'
        );
        $statement->execute([
            'block_id' => $id,
            'starts_at' => $start,
            'ends_at' => $end,
            'reason' => $reason,
        ]);

        if ($statement->rowCount() > 0) {
            return true;
        }

        $exists = $this->connection->prepare(
            'SELECT 1 FROM reservation_block WHERE block_id = :block_id LIMIT 1'
        );
        $exists->execute(['block_id' => $id]);

        return $exists->fetchColumn() !== false;
    }

    public function deleteBlockedPeriod(int $id): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM reservation_block WHERE block_id = :block_id'
        );
        $statement->execute(['block_id' => $id]);

        return $statement->rowCount() > 0;
    }
}
