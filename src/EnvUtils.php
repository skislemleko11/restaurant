<?php
declare(strict_types=1);

namespace App;

use UnexpectedValueException;

final class EnvUtils
{
    public static function getRequiredEnvValue(string $key): string
    {
        if (!isset($_ENV[$key])) {
            throw new UnexpectedValueException("Required environment variable not set: $key");
        }

        return trim((string)($_ENV[$key]));
    }
}