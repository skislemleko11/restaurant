<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepositoryInterface;

final readonly class AuthService
{
    public const string USER_ID_SESSION_KEY = 'user_id';

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function authenticate(string $email, string $password): ?int
    {
        $user = $this->userRepository->findVerifiedByEmail($email);
        if ($user === null || !password_verify($password, $user->passwordHash())) {
            return null;
        }

        return $user->id();
    }
}