<?php

namespace Core;

class Validator
{
    public static function requireString(array $data, string $key, string $label, int $min = 1, int $max = 255): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));
        $length = mb_strlen($value);
        if ($length < $min) {
            return $label . ' is required.';
        }
        if ($length > $max) {
            return $label . ' must be at most ' . $max . ' characters.';
        }
        return null;
    }

    public static function optionalUrl(array $data, string $key, string $label): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));
        if ($value === '') {
            return null;
        }
        if (!filter_var($value, FILTER_VALIDATE_URL) && !str_starts_with($value, '/uploads/')) {
            return $label . ' must be a valid URL or uploaded path.';
        }
        return null;
    }

    public static function category(array $data, string $key = 'category'): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));
        if ($value === '') {
            return 'Category is required.';
        }
        if (mb_strlen($value) > 60) {
            return 'Category must be at most 60 characters.';
        }
        return null;
    }
}
