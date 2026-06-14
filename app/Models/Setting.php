<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const KEY_COMPANY_LOGO = 'company_logo_path';
    public const KEY_COMPANY_NAME = 'company_name';
    public const KEY_COMPANY_TAGLINE = 'company_tagline';

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            return self::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    /**
     * Absolute filesystem path to the company logo, suitable for
     * embedding in DomPDF-rendered views (DomPDF needs a local file
     * path or base64, not a public URL).
     *
     * Returns null if no logo has been uploaded.
     */
    public static function logoAbsolutePath(): ?string
    {
        $path = self::get(self::KEY_COMPANY_LOGO);

        if (! $path) {
            return null;
        }

        $absolute = Storage::disk('public')->path($path);

        return file_exists($absolute) ? $absolute : null;
    }

    public static function companyName(): string
    {
        return self::get(self::KEY_COMPANY_NAME, 'NovarexHSE TZ');
    }

    public static function companyTagline(): string
    {
        return self::get(self::KEY_COMPANY_TAGLINE, 'HSE & Sustainability Consulting - Mwanza, Tanzania');
    }
}
