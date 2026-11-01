<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SpreadsheetReader
{
    public static function read(UploadedFile $file): array
    {
        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $data = $sheet->toArray(null, true, false, false);

        if ($data === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(fn ($header) => self::normalize($header), array_shift($data));
        $rows = [];

        foreach ($data as $index => $values) {
            if (self::blankRow($values)) {
                continue;
            }

            $row = [];
            foreach ($headers as $position => $header) {
                if ($header !== '') {
                    $row[$header] = $values[$position] ?? null;
                }
            }

            $rows[] = ['number' => $index + 2, 'data' => $row];
        }

        return [
            'headers' => array_values(array_filter($headers)),
            'rows' => $rows,
        ];
    }

    public static function normalize(mixed $header): string
    {
        $value = Str::ascii(strtolower(trim((string) $header)));

        return trim((string) preg_replace('/[^a-z0-9]+/', '_', $value), '_');
    }

    public static function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    private static function blankRow(array $values): bool
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return false;
            }
        }

        return true;
    }
}
