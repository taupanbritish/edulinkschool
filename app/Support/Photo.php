<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Photo
{
    public static function update(?UploadedFile $file, ?string $existing, bool $remove, string $folder): ?string
    {
        if ($file) {
            self::delete($existing);

            return $file->store($folder, 'public');
        }

        if ($remove) {
            self::delete($existing);

            return null;
        }

        return $existing;
    }

    public static function delete(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
