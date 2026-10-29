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
}
