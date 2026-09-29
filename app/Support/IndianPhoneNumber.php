<?php

namespace App\Support;

class IndianPhoneNumber
{
    public static function normalize(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return strlen($digits) === 10 ? '91'.$digits : $digits;
    }

    public static function isValid(string $value): bool
    {
        return preg_match('/^91[6-9][0-9]{9}$/', $value) === 1;
    }
}
