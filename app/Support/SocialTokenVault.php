<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

class SocialTokenVault
{
    public static function encrypt(?string $plain): string
    {
        if ($plain === null || $plain === '') {
            return '';
        }

        return Crypt::encryptString($plain);
    }

    public static function decrypt(?string $stored): string
    {
        if ($stored === null || $stored === '') {
            return '';
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            return is_string($stored) ? $stored : '';
        }
    }
}
