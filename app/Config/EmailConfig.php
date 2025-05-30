<?php

namespace App\Config;

class EmailConfig
{
    public static function get(string $key): ?string
    {
        return $_ENV[$key] ?? null;
    }
}
