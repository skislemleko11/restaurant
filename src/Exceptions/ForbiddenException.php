<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Velo\Exceptions\HttpResponseExceptionInterface;

class ForbiddenException extends Exception implements HttpResponseExceptionInterface
{
    public function getStatusCode(): int
    {
        return 403;
    }

    public function shouldLogException(): bool
    {
        return false;
    }

    public function getPublicMessage(): string
    {
        return 'Forbidden';
    }
}