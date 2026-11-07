<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
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

    public static function logoDataUri(): ?string
    {
        $path = self::get('logo_path');

        if (blank($path)) {
            return null;
        }

        try {
            $disk = Storage::disk('public');

            if (! $disk->exists($path)) {
                return null;
            }

            $mime = $disk->mimeType($path);

            if (! $mime || ! str_starts_with($mime, 'image/')) {
                return null;
            }

            $data = $disk->get($path);

            if (function_exists('imagecreatefromstring') && ($image = @imagecreatefromstring($data)) !== false) {
                $data = self::shrinkImage($image);
                $mime = 'image/png';
            }

            return 'data:'.$mime.';base64,'.base64_encode($data);
        } catch (Throwable) {
            return null;
        }
    }

    private static function shrinkImage(\GdImage $image, int $max = 160): string
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $max || $height > $max) {
            $scale = $max / max($width, $height);
            $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale), IMG_BILINEAR_FIXED);

            if ($resized !== false) {
                $image = $resized;
            }
        }

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }
}
