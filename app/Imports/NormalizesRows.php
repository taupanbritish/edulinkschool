<?php

namespace App\Imports;

trait NormalizesRows
{
    protected function text(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value) && floor($value) === $value) {
            return number_format($value, 0, '', '');
        }

        return trim((string) $value);
    }

    protected function gender(mixed $value): string
    {
        $label = strtolower($this->text($value));

        return match ($label) {
            'l', 'lk', 'laki-laki', 'laki laki', 'pria' => 'L',
            'p', 'pr', 'perempuan', 'wanita' => 'P',
            default => '',
        };
    }
}
