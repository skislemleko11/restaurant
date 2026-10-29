<?php
declare(strict_types=1);

namespace App\Entities;

final readonly class User
{
    public function __construct(
        private int $id,
        private string $passwordHash
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }
}
