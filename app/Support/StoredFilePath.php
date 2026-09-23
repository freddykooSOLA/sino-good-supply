<?php

namespace App\Support;

class StoredFilePath
{
    public static function normalize(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = collect($value)
                ->flatten()
                ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                ->first();
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || $value === '[]' || $value === 'null') {
            return null;
        }

        if (str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return self::normalize($decoded);
            }
        }

        return ltrim($value, '/');
    }
}
