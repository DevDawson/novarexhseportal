<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Shared helper to repair invalid UTF-8 strings before they are passed to
 * DomPDF (which throws "Malformed UTF-8 characters, possibly incorrectly
 * encoded" if any string in the rendered view contains invalid byte
 * sequences). This commonly happens with names/text originally entered
 * via Windows using a non-UTF-8 codepage (Windows-1252 / Latin-1) and
 * stored as-is in MySQL.
 *
 * Works on all PHP versions (uses iconv, not mb_scrub which requires PHP 8.3+).
 */
class Utf8Sanitizer
{
    /**
     * Recursively sanitize a value: Collections, arrays, and scalars.
     */
    public static function clean(mixed $data): mixed
    {
        if ($data instanceof Collection) {
            return $data->map(fn ($item) => self::clean($item));
        }

        if (is_array($data)) {
            return array_map(fn ($item) => self::clean($item), $data);
        }

        if (is_string($data)) {
            return self::cleanString($data);
        }

        return $data;
    }

    /**
     * Sanitize a single string value.
     */
    public static function cleanString(string $value): string
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');

            if ($converted !== false) {
                $value = $converted;
            }
        }

        $scrubbed = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $scrubbed !== false ? $scrubbed : $value;
    }
}
