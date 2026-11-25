<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\User;
use PDO;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $connection)
    {
    }

    public function findVerifiedByEmail(string $email): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT account_id, password_hash
             FROM users
             WHERE email = :email
               AND verfication_token_used_at IS NOT NULL
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        if (!is_array($user)) {
            return null;
        }

        return new User(
            (int)$user['account_id'],
            (string)$user['password_hash']
        );
    }

    public function findVerifiedAccountIdByEmail(string $email): ?int
    {
        $statement = $this->connection->prepare(
            'SELECT account_id
             FROM users
             WHERE email = :email
               AND verfication_token_used_at IS NOT NULL
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $accountId = $statement->fetchColumn();

        return $accountId === false ? null : (int)$accountId;
    }

    public function create(
        string $email,
        string $passwordHash,
        string $verificationToken,
        string $expiresAt
    ): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (email, password_hash, verification_token, verification_token_expires_at)
             VALUES (:email, :password_hash, :verification_token, :expires_at)'
        );
        $statement->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'verification_token' => $verificationToken,
            'expires_at' => $expiresAt,
        ]);
    }

    public function verifyEmail(string $token): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE users
             SET verfication_token_used_at = UTC_TIMESTAMP()
             WHERE verification_token = :token
               AND verification_token_expires_at > UTC_TIMESTAMP()
               AND verfication_token_used_at IS NULL'
        );
        $statement->execute(['token' => $token]);

        return $statement->rowCount() === 1;
    }

    public function updatePassword(int $accountId, string $passwordHash): void
    {
        $statement = $this->connection->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE account_id = :account_id'
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'account_id' => $accountId,
        ]);
    }

    public function isAdmin(int $accountId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1 FROM users WHERE account_id = :account_id AND role = :role LIMIT 1'
        );
        $statement->execute(['account_id' => $accountId, 'role' => 'admin']);

        return $statement->fetchColumn() !== false;
    }

    public function findEmailByAccountId(int $accountId): ?string
    {
        $statement = $this->connection->prepare(
            'SELECT email FROM users WHERE account_id = :account_id LIMIT 1'
        );
        $statement->execute(['account_id' => $accountId]);
        $email = $statement->fetchColumn();

        return $email === false ? null : (string)$email;
    }
}
