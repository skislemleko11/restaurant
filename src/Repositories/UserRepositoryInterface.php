<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\User;

interface UserRepositoryInterface
{
    public function findVerifiedByEmail(string $email): ?User;
}
