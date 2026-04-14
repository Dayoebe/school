<?php

namespace App\Support;

class SafeHtml
{
    public static function math(?string $value): string
    {
        return nl2br(e((string) $value), false);
    }
}
