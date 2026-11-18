<?php

namespace App\Support;

class Rupiah
{
    public static function normalize(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^(?:Rp\s*)?(?:[0-9]{1,3}(?:\.[0-9]{3})+|[0-9]+)$/i', $value)) {
            return preg_replace('/[^0-9]/', '', $value);
        }

        return $value;
    }
}
