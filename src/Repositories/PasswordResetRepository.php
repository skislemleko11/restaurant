<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final readonly class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    public function __construct(private PDO $connection)
    {
    }

    public function save(int $accountId, string $token, string $expiresAt): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO password_reset (account_id, password_reset_token, password_reset_token_expires_at)
             VALUES (:account_id, :token, :expires_at)
             ON DUPLICATE KEY UPDATE
                password_reset_token = VALUES(password_reset_token),
                password_reset_token_expires_at = VALUES(password_reset_token_expires_at)'
        );
        $statement->execute([
            'account_id' => $accountId,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findAccountIdByValidToken(string $token): ?int
    {
        $statement = $this->connection->prepare(
            'SELECT account_id
             FROM password_reset
             WHERE password_reset_token = :token
               AND password_reset_token_expires_at > UTC_TIMESTAMP()
             LIMIT 1'
        );
        $statement->execute(['token' => $token]);
        $accountId = $statement->fetchColumn();

        return $accountId === false ? null : (int)$accountId;
    }

    public function deleteByAccountId(int $accountId): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM password_reset WHERE account_id = :account_id'
        );
        $statement->execute(['account_id' => $accountId]);
    }
}
