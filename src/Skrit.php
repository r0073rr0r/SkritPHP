<?php

declare(strict_types=1);

namespace Skrit;

use Skrit\Core\CaseHelper;
use Skrit\Core\StringUtils;
use Skrit\Core\Tokenizer;
use Skrit\Core\Transliteration;

final class Skrit
{
    public static function _looksLikeUtroWord(string $word): bool
    {
        $normalized = mb_strtolower(Transliteration::cyrillicToLatin($word), 'UTF-8');
        return StringUtils::startsWith($normalized, 'u')
            && str_contains($normalized, 'za')
            && StringUtils::endsWith($normalized, 'nje');
    }

    public static function _looksLikeUtroLeet(string $text): bool
    {
        $lowered = mb_strtolower($text, 'UTF-8');
        $hasPrefix = str_contains($lowered, '00');
        $hasMiddle = str_contains($lowered, '24') || str_contains($lowered, 'z4');
        $hasSuffix = str_contains($lowered, 'n73') || str_contains($lowered, 'nj3') || str_contains($text, 'њ');
        return $hasPrefix && $hasMiddle && $hasSuffix;
    }

    public static function _looksLikeUtrovacki(
        string $text,
        int $minWordLength = 3,
        string $plainCTarget = 'ц',
        bool $softTjToCyrillic = false,
        string $utroPrefix = 'u',
        string $utroInfix = 'za',
        string $utroSuffix = 'nje',
    ): bool {
        $utro = new Utrovacki(
            minWordLength: $minWordLength,
            plainCTarget: $plainCTarget,
            softTjToCyrillic: $softTjToCyrillic,
            prefix: $utroPrefix,
            infix: $utroInfix,
            suffix: $utroSuffix
        );

        $words = [];
        foreach (Tokenizer::splitWordOrOther($text) as $part) {
            if (Tokenizer::isAlpha($part) && StringUtils::length($part) >= $minWordLength) {
                $words[] = $part;
            }
        }

        if ($words === []) {
            return false;
        }

        $decodableWords = 0;
        foreach ($words as $word) {
            if ($utro->canDecodeWord($word)) {
                $decodableWords++;
            }
        }

        return $decodableWords > 0 && ($decodableWords / count($words)) >= 0.5;
    }

    public static function _looksLikeLeetrovacki(string $text): bool
    {
        if (Leet::looksLikeLeet($text) || self::_looksLikeUtroLeet($text)) {
            return true;
        }

        preg_match_all('/\S+/u', $text, $matches);
        $tokens = $matches[0] ?? [];
        if ($tokens === []) {
            return false;
        }

        $symbolChars = array_flip(array_values(array_diff(Leet::LEET_SIGNAL_CHARS, str_split('0123456789'))));
        $leetTokens = 0;
        foreach ($tokens as $token) {
            if (self::isLeetToken($token, $symbolChars)) {
                $leetTokens++;
            }
        }

        return $leetTokens > 0 && ($leetTokens / count($tokens)) >= 0.5;
    }

    public static function detectLeetBase(string $text): string
    {
        if (self::_looksLikeUtroLeet($text)) {
            return 'utro';
        }

        $words = [];
        foreach (Tokenizer::splitWordOrOther($text) as $part) {
            if (Tokenizer::isAlpha($part)) {
                $words[] = $part;
            }
        }

        if ($words === []) {
            return 'satro';
        }

        $utroWords = 0;
        foreach ($words as $word) {
            if (self::_looksLikeUtroWord($word)) {
                $utroWords++;
            }
        }

        if ($utroWords > 0 && ($utroWords / count($words)) >= 0.5) {
            return 'utro';
        }

        return 'satro';
    }

    public static function detectMode(
        string $text,
        int $minWordLength = 3,
        string $plainCTarget = 'ц',
        bool $softTjToCyrillic = false,
        string $utroPrefix = 'u',
        string $utroInfix = 'za',
        string $utroSuffix = 'nje',
    ): string {
        if (self::_looksLikeLeetrovacki($text)) {
            return 'leet';
        }

        if (
            self::_looksLikeUtrovacki(
                text: $text,
                minWordLength: $minWordLength,
                plainCTarget: $plainCTarget,
                softTjToCyrillic: $softTjToCyrillic,
                utroPrefix: $utroPrefix,
                utroInfix: $utroInfix,
                utroSuffix: $utroSuffix
            )
        ) {
            return 'utro';
        }

        return 'satro';
    }

    public static function _looksLikeSatroEncoded(
        string $text,
        int $minWordLength = 3,
        string $plainCTarget = 'ц',
        bool $softTjToCyrillic = false,
    ): bool {
        $satro = new Satrovacki(
            minWordLength: $minWordLength,
            plainCTarget: $plainCTarget,
            softTjToCyrillic: $softTjToCyrillic
        );

        $words = [];
        foreach (Tokenizer::splitWordOrOther($text) as $part) {
            if (Tokenizer::isAlpha($part) && StringUtils::length($part) >= $minWordLength) {
                $words[] = $part;
            }
        }

        if ($words === []) {
            return false;
        }

        $decodableWords = [];
        foreach ($words as $word) {
            if ($satro->canDecodeWord($word)) {
                $decodableWords[] = $word;
            }
        }

        if ($decodableWords === []) {
            return false;
        }

        if ((count($decodableWords) / count($words)) < 0.5) {
            return false;
        }

        $decodedPairs = [];
        foreach ($words as $original) {
            $decoded = $satro->decodeWord($original);
            if (mb_strtolower($original, 'UTF-8') !== mb_strtolower($decoded, 'UTF-8')) {
                $decodedPairs[] = [$original, $decoded];
            }
        }

        if ($decodedPairs === []) {
            return false;
        }

        $improvedWords = 0;
        foreach ($decodedPairs as [$original, $decoded]) {
            if ($satro->canDecodeWord($original) && !$satro->canDecodeWord($decoded)) {
                $improvedWords++;
            }
        }

        return $improvedWords > 0;
    }

    public static function _deleetTextBasic(string $text): string
    {
        $multi = [
            ['n73', 'nje'],
            ['nj3', 'nje'],
            ['њ', 'nje'],
            ['z4', 'za'],
            ['24', 'za'],
            ['00', 'u'],
        ];
        $single = [
            '0' => 'o',
            '1' => 'i',
            '2' => 'z',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '6' => 'g',
            '7' => 't',
            '8' => 'b',
            '9' => 'p',
            '$' => 's',
        ];

        preg_match_all('/\S+|\s+/u', $text, $matches);
        $parts = $matches[0] ?? [];
        $transformed = [];

        foreach ($parts as $part) {
            if (preg_match('/^\s+$/u', $part) === 1) {
                $transformed[] = $part;
                continue;
            }

            $word = mb_strtolower($part, 'UTF-8');
            foreach ($multi as [$src, $dst]) {
                $word = str_replace($src, $dst, $word);
            }

            $chars = [];
            foreach (StringUtils::splitChars($word) as $char) {
                $chars[] = $single[$char] ?? $char;
            }

            $joined = implode('', $chars);
            if (CaseHelper::isUpper($part)) {
                $transformed[] = mb_strtoupper($joined, 'UTF-8');
            } elseif (CaseHelper::isTitle($part)) {
                $first = StringUtils::substr($joined, 0, 1);
                $rest = StringUtils::substr($joined, 1);
                $transformed[] = mb_strtoupper($first, 'UTF-8') . mb_strtolower($rest, 'UTF-8');
            } else {
                $transformed[] = $joined;
            }
        }

        return implode('', $transformed);
    }

    /**
     * @return array{0:string,1:string}
     */
    public static function encodeText(
        string $text,
        string $mode = 'auto',
        ?string $detectFrom = null,
        int $minWordLength = 3,
        string $plainCTarget = 'ц',
        bool $softTjToCyrillic = false,
        string $leetBase = 'auto',
        string $leetProfile = 'basic',
        int $leetComplexity = 0,
        float $leetDensity = Leet::DEFAULT_LEET_DENSITY,
        string $zaStyle = '24',
        string $njeStyle = 'n73',
        string $utroPrefix = 'u',
        string $utroInfix = 'za',
        string $utroSuffix = 'nje',
    ): array {
        $referenceText = $text;

        if ($mode === 'auto') {
            $referenceText = $detectFrom ?? $text;
            $resolvedMode = self::detectMode(
                text: $referenceText,
                minWordLength: $minWordLength,
                plainCTarget: $plainCTarget,
                softTjToCyrillic: $softTjToCyrillic,
                utroPrefix: $utroPrefix,
                utroInfix: $utroInfix,
                utroSuffix: $utroSuffix
            );
        } else {
            $resolvedMode = $mode;
        }

        if ($resolvedMode === 'satro') {
            $encoder = new Satrovacki(
                minWordLength: $minWordLength,
                plainCTarget: $plainCTarget,
                softTjToCyrillic: $softTjToCyrillic
            );

            if (
                $mode === 'auto'
                && $detectFrom === null
                && self::_looksLikeSatroEncoded(
                    text: $referenceText,
                    minWordLength: $minWordLength,
                    plainCTarget: $plainCTarget,
                    softTjToCyrillic: $softTjToCyrillic
                )
            ) {
                return [$encoder->decode($text), $resolvedMode];
            }

            return [$encoder->encode($text), $resolvedMode];
        }

        if ($resolvedMode === 'utro') {
            $encoder = new Utrovacki(
                minWordLength: $minWordLength,
                plainCTarget: $plainCTarget,
                softTjToCyrillic: $softTjToCyrillic,
                prefix: $utroPrefix,
                infix: $utroInfix,
                suffix: $utroSuffix
            );

            if (
                $mode === 'auto'
                && $detectFrom === null
                && self::_looksLikeUtrovacki(
                    text: $referenceText,
                    minWordLength: $minWordLength,
                    plainCTarget: $plainCTarget,
                    softTjToCyrillic: $softTjToCyrillic,
                    utroPrefix: $utroPrefix,
                    utroInfix: $utroInfix,
                    utroSuffix: $utroSuffix
                )
            ) {
                return [$encoder->decode($text), $resolvedMode];
            }

            return [$encoder->encode($text), $resolvedMode];
        }

        $resolvedLeetBase = $leetBase;
        if ($resolvedMode === 'leet' && $leetBase === 'auto') {
            $resolvedLeetBase = self::detectLeetBase($referenceText);
        }

        if ($mode === 'auto' && $detectFrom === null && self::_looksLikeLeetrovacki($referenceText)) {
            $deleeted = self::_deleetTextBasic($text);

            if ($resolvedLeetBase === 'utro') {
                $utro = new Utrovacki(
                    minWordLength: $minWordLength,
                    plainCTarget: $plainCTarget,
                    softTjToCyrillic: $softTjToCyrillic,
                    prefix: $utroPrefix,
                    infix: $utroInfix,
                    suffix: $utroSuffix
                );

                if (
                    self::_looksLikeUtrovacki(
                        text: $deleeted,
                        minWordLength: $minWordLength,
                        plainCTarget: $plainCTarget,
                        softTjToCyrillic: $softTjToCyrillic,
                        utroPrefix: $utroPrefix,
                        utroInfix: $utroInfix,
                        utroSuffix: $utroSuffix
                    )
                ) {
                    return [$utro->decode($deleeted), $resolvedMode];
                }
            }

            $satro = new Satrovacki(
                minWordLength: $minWordLength,
                plainCTarget: $plainCTarget,
                softTjToCyrillic: $softTjToCyrillic
            );

            if (
                self::_looksLikeSatroEncoded(
                    text: $deleeted,
                    minWordLength: $minWordLength,
                    plainCTarget: $plainCTarget,
                    softTjToCyrillic: $softTjToCyrillic
                )
            ) {
                return [$satro->decode($deleeted), $resolvedMode];
            }

            return [$deleeted, $resolvedMode];
        }

        $encoder = new Leetrovacki(
            baseMode: $resolvedLeetBase,
            minWordLength: $minWordLength,
            plainCTarget: $plainCTarget,
            softTjToCyrillic: $softTjToCyrillic,
            leetProfile: $leetProfile,
            leetComplexity: $leetComplexity,
            leetDensity: $leetDensity,
            zaStyle: $zaStyle,
            njeStyle: $njeStyle
        );

        return [$encoder->encode($text), $resolvedMode];
    }

    /**
     * @param array<string, true> $symbolChars
     */
    private static function isLeetToken(string $token, array $symbolChars): bool
    {
        $hasAlpha = false;
        $hasDigit = false;
        $hasSymbol = false;

        foreach (StringUtils::splitChars($token) as $char) {
            if (preg_match('/\p{L}/u', $char) === 1) {
                $hasAlpha = true;
            }
            if (preg_match('/\d/u', $char) === 1) {
                $hasDigit = true;
            }
            if (isset($symbolChars[$char]) || $char === '`' || $char === '$') {
                $hasSymbol = true;
            }
        }

        return ($hasDigit && ($hasAlpha || $hasSymbol)) || ($hasAlpha && $hasSymbol);
    }
}
