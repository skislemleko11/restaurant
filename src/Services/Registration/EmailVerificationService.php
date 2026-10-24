<?php
declare(strict_types=1);

namespace App\Services\Registration;

use PDO;

final readonly class EmailVerificationService
{
    public function __construct(private PDO $connection)
    {
    }

    public function verify(string $token): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }

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
}
