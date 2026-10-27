<?php

namespace App\Support\Tokens;

use InvalidArgumentException;

final class OpaqueToken
{
    public static function issue(int $bytes = 32): array
    {
        if ($bytes < 16) {
            throw new InvalidArgumentException('Token minimal 128 bit (16 byte).');
        }

        $plain = rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');

        return ['plain' => $plain, 'hash' => hash('sha256', $plain, true)];
    }

    public static function hash(string $plain): string
    {
        return hash('sha256', $plain, true);
    }
}