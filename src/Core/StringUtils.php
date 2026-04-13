<?php

declare(strict_types=1);

namespace Skrit\Core;

final class StringUtils
{
    public static function length(string $text): int
    {
        return mb_strlen($text, 'UTF-8');
    }

    public static function substr(string $text, int $start, ?int $length = null): string
    {
        if ($length === null) {
            return mb_substr($text, $start, null, 'UTF-8');
        }

        return mb_substr($text, $start, $length, 'UTF-8');
    }

    /**
     * @return list<string>
     */
    public static function splitChars(string $text): array
    {
        if ($text === '') {
            return [];
        }

        $parts = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        return $parts === false ? [] : array_values($parts);
    }

    public static function startsWith(string $text, string $prefix): bool
    {
        if ($prefix === '') {
            return true;
        }

        return self::substr($text, 0, self::length($prefix)) === $prefix;
    }

    public static function endsWith(string $text, string $suffix): bool
    {
        if ($suffix === '') {
            return true;
        }

        $suffixLength = self::length($suffix);
        if ($suffixLength > self::length($text)) {
            return false;
        }

        return self::substr($text, -$suffixLength) === $suffix;
    }
}
