<?php

declare(strict_types=1);

namespace Skrit\Core;

final class Tokenizer
{
    public const WORD_OR_OTHER_PATTERN = '/[^\W\d_]+|\d+|\s+|./u';

    /**
     * @return list<string>
     */
    public static function splitWordOrOther(string $text): array
    {
        if ($text === '') {
            return [];
        }

        preg_match_all(self::WORD_OR_OTHER_PATTERN, $text, $matches);
        return $matches[0] ?? [];
    }

    public static function isAlpha(string $token): bool
    {
        return $token !== '' && preg_match('/^\p{L}+$/u', $token) === 1;
    }
}
