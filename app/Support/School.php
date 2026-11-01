<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

class School
{
    private static ?array $settings = null;

    public static function all(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        try {
            self::$settings = Setting::query()->pluck('value', 'key')->all();
        } catch (Throwable) {
            self::$settings = [];
        }

        return self::$settings;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default;
    }

    public static function forget(): void
    {
        self::$settings = null;
    }

    public static function label(): string
    {
        return self::get('school_short', self::get('school_name', 'Sekolah'));
    }
}
