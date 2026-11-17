<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\User;

interface UserRepositoryInterface
{
    public function findVerifiedByEmail(string $email): ?User;

    public function findVerifiedAccountIdByEmail(string $email): ?int;

    public function create(
        string $email,
        string $passwordHash,
        string $verificationToken,
        string $expiresAt
    ): void;

    public function verifyEmail(string $token): bool;

    public function updatePassword(int $accountId, string $passwordHash): void;

    public function isAdmin(int $accountId): bool;
}
