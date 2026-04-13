<?php

declare(strict_types=1);

namespace Skrit\Core;

final class CaseHelper
{
    public static function applyCase(string $original, string $transformed): string
    {
        if (self::isUpper($original)) {
            return mb_strtoupper($transformed, 'UTF-8');
        }

        if (self::isTitle($original)) {
            $first = StringUtils::substr($transformed, 0, 1);
            $rest = StringUtils::substr($transformed, 1);
            return mb_strtoupper($first, 'UTF-8') . mb_strtolower($rest, 'UTF-8');
        }

        if (self::isLower($original)) {
            return mb_strtolower($transformed, 'UTF-8');
        }

        return $transformed;
    }

    public static function isUpper(string $text): bool
    {
        if ($text === '' || !preg_match('/\p{L}/u', $text)) {
            return false;
        }

        return mb_strtoupper($text, 'UTF-8') === $text && mb_strtolower($text, 'UTF-8') !== $text;
    }

    public static function isLower(string $text): bool
    {
        if ($text === '' || !preg_match('/\p{L}/u', $text)) {
            return false;
        }

        return mb_strtolower($text, 'UTF-8') === $text && mb_strtoupper($text, 'UTF-8') !== $text;
    }

    public static function isTitle(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        $hasCased = false;
        $previousWasCased = false;

        foreach (StringUtils::splitChars($text) as $char) {
            $upper = mb_strtoupper($char, 'UTF-8');
            $lower = mb_strtolower($char, 'UTF-8');
            $isCased = $upper !== $lower;

            if (!$isCased) {
                $previousWasCased = false;
                continue;
            }

            $hasCased = true;
            if (!$previousWasCased) {
                if ($char !== $upper) {
                    return false;
                }
            } else {
                if ($char !== $lower) {
                    return false;
                }
            }

            $previousWasCased = true;
        }

        return $hasCased;
    }
}
