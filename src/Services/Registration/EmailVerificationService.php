<?php
declare(strict_types=1);

namespace App\Services\Registration;

use App\Repositories\UserRepositoryInterface;

final readonly class EmailVerificationService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function verify(string $token): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }

        return $this->userRepository->verifyEmail($token);
    }
}
