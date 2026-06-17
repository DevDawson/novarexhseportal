<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const KEY_COMPANY_LOGO    = 'company_logo_path';
    public const KEY_COMPANY_NAME    = 'company_name';
    public const KEY_COMPANY_TAGLINE = 'company_tagline';
    public const KEY_COMPANY_ADDRESS = 'company_address';
    public const KEY_COMPANY_TIN     = 'company_tin';
    public const KEY_COMPANY_PHONE   = 'company_phone';
    public const KEY_COMPANY_EMAIL   = 'company_email';

    public const KEY_BANK_NAME           = 'company_bank_name';
    public const KEY_BANK_BRANCH         = 'company_bank_branch';
    public const KEY_BANK_ACCOUNT_NAME   = 'company_bank_account_name';
    public const KEY_BANK_ACCOUNT_NUMBER = 'company_bank_account_number';
    public const KEY_BANK_SWIFT          = 'company_bank_swift';

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

    public static function companyAddress(): string
    {
        return self::get(self::KEY_COMPANY_ADDRESS, '');
    }

    public static function companyTin(): string
    {
        return self::get(self::KEY_COMPANY_TIN, '');
    }

    public static function companyPhone(): string
    {
        return self::get(self::KEY_COMPANY_PHONE, '');
    }

    public static function companyEmail(): string
    {
        return self::get(self::KEY_COMPANY_EMAIL, '');
    }

    public static function bankDetails(): array
    {
        return [
            'name'           => self::get(self::KEY_BANK_NAME, ''),
            'branch'         => self::get(self::KEY_BANK_BRANCH, ''),
            'account_name'   => self::get(self::KEY_BANK_ACCOUNT_NAME, ''),
            'account_number' => self::get(self::KEY_BANK_ACCOUNT_NUMBER, ''),
            'swift'          => self::get(self::KEY_BANK_SWIFT, ''),
        ];
    }
}
