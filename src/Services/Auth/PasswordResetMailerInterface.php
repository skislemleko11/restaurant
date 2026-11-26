<?php
declare(strict_types=1);

namespace App\Services\Auth;

interface PasswordResetMailerInterface
{
    /**
     * @throws PasswordResetException
     */
    public function send(string $recipient, string $token): void;
}
