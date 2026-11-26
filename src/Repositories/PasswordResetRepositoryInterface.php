<?php
declare(strict_types=1);

namespace App\Repositories;

interface PasswordResetRepositoryInterface
{
    public function save(int $accountId, string $token, string $expiresAt): void;

    public function findAccountIdByValidToken(string $token): ?int;

    public function deleteByAccountId(int $accountId): void;
}
