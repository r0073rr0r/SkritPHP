<?php

declare(strict_types=1);

namespace Skrit;

use InvalidArgumentException;
use Skrit\Core\CaseHelper;
use Skrit\Core\StringUtils;

final class Leet
{
    public const DEFAULT_LEET_DENSITY = 0.86;

    /** @var array<string, list<string>> */
    public const LEET_TABLE = [
        'a' => ['4', '/\\', '@', '^', '(L', '/-\\'],
        'b' => ['I3', '8', '13', '|3', '!3', '(3', '/3', ')3', '|-]', 'j3'],
        'c' => ['[', '<', '(', '{'],
        'd' => [')', '|)', '(|', '[)', 'I>', '|>', 'T)', 'I7', 'cl', '|}', '|]', 'l)', 'I)'],
        'e' => ['3', '&', '[-', '|=-'],
        'f' => ['|=', '|#', 'ph', '/=', 'v'],
        'g' => ['6', '&', '(_+', '9', 'C-', 'gee', '(?,', '[,', '{,', '<-', '(.'],
        'h' => ['#', '/-/', '\\-\\', '[-]', ']-[', ')-(', '(-)', ':-:', '|~|', '|-|', ']~[', '}{', '!-!', '1-1', '\\-/', 'I+I'],
        'i' => ['1', '|', '][', '!', 'eye', '3y3'],
        'j' => [',_|', '_|', '._|', '._]', '_]', ',_]', ']'],
        'k' => ['>|', '|<', '1<', '|c', '|('],
        'l' => ['1', '7', '2', '|_', '|'],
        'm' => ['/\\/\\', '/V\\', '[V]', '|\\/|', '^^', '<\\/>', '{V}', '(v)', '(V)', '|\\|\\', ']\\/[', 'nn', '11'],
        'n' => ['^/', '|\\|', '/\\/', '[\\]', '<\\>', '{\\}', '/V', '^', '|V'],
        'o' => ['0', '()', 'oh', '[]', '<>'],
        'p' => ['|*', '|o', '|^', '|>', '|"', '9', '[]D', '|7', '|0'],
        'q' => ['(_,)', '()_', '2', '0_', '<|', '&', '9', '0|'],
        'r' => ['I2', '9', '|`', '|~', '|?', '/2', '|^', 'lz', 'l2', '7', '2', '12', '[z', '|-', '|2'],
        's' => ['5', '$', 'z', 'ehs', 'es', '2'],
        't' => ['7', '+', '-|-', "']['", '~|~'],
        'u' => ['(_)', '|_|', 'v', 'L|'],
        'v' => ['\\/', '|/', '\\|'],
        'w' => ['\\/\\/', '|/\\|', 'vv', '\\N', "'//", "\\\\'", '\\^/', '(n)', '\\V/', '\\X/', '\\|/', '\\_|_/', '\\_:_/', 'uu', '2u', '\\\\//\\\\//'],
        'x' => ['><', '}{', 'ecks', ')(', ']['],
        'y' => ['j', '`/', '\\|/', '\\//', "'/"],
        'z' => ['2', '7_', '-/_', '%'],
    ];

    /** @var array<string, string> */
    public const BASIC_LEET_PROFILE = [
        'a' => '4',
        'b' => '8',
        'e' => '3',
        'g' => '6',
        'i' => '1',
        'o' => '0',
        's' => '5',
        't' => '7',
        'u' => '00',
        'z' => '2',
    ];

    /** @var array<string, string> */
    public const READABLE_FULL_PROFILE = [
        'a' => '4',
        'b' => '8',
        'c' => '(',
        'd' => '|)',
        'e' => '3',
        'f' => 'ph',
        'g' => '6',
        'h' => '#',
        'i' => '1',
        'j' => '_|',
        'k' => '|<',
        'l' => '1',
        'm' => '^^',
        'n' => '^/',
        'o' => '0',
        'p' => '9',
        'q' => '0_',
        'r' => 'ri2',
        's' => '5',
        't' => '7',
        'u' => '00',
        'v' => '\\/',
        'w' => 'vv',
        'x' => '><',
        'y' => '`/',
        'z' => '2',
    ];

    /** @var list<string> */
    public const LEET_SIGNAL_CHARS = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        '@', '$', '!', '+', '|', '\\', '/', '(', ')', '[', ']',
        '{', '}', '<', '>', '^', '_', '-', '*', '#', '%',
    ];

    /**
     * @return array<string, string>
     */
    public static function buildFullLeetProfile(int $complexity = 0): array
    {
        if ($complexity < 0) {
            throw new InvalidArgumentException('complexity must be a non-negative integer');
        }

        $profile = [];
        foreach (self::LEET_TABLE as $letter => $variants) {
            $index = min($complexity, count($variants) - 1);
            $profile[$letter] = $variants[$index];
        }

        if ($complexity === 0) {
            $profile['r'] = 'ri2';
        }

        return $profile;
    }

    /**
     * @return list<string>
     */
    public static function availableProfiles(): array
    {
        $names = array_keys(self::leetProfiles());
        sort($names);
        return array_values($names);
    }

    /**
     * @param array<string, string>|null $customMap
     * @return array<string, string>
     */
    public static function getLeetProfile(string $name = 'basic', ?array $customMap = null, int $complexity = 0): array
    {
        if ($customMap !== null) {
            $normalized = [];
            foreach ($customMap as $key => $value) {
                $normalized[mb_strtolower((string) $key, 'UTF-8')] = (string) $value;
            }
            return $normalized;
        }

        if (mb_strtolower($name, 'UTF-8') === 'full') {
            return self::buildFullLeetProfile($complexity);
        }

        $profiles = self::leetProfiles();
        $profile = $profiles[mb_strtolower($name, 'UTF-8')] ?? null;
        if ($profile === null) {
            $valid = implode(', ', self::availableProfiles());
            throw new InvalidArgumentException("Unknown leet profile '{$name}'. Valid profiles: {$valid}");
        }

        return $profile;
    }

    /**
     * @param array<string, string> $mapping
     */
    public static function applyLeet(string $text, array $mapping, float $density = self::DEFAULT_LEET_DENSITY): string
    {
        if ($density < 0.0 || $density > 1.0) {
            throw new InvalidArgumentException('density must be between 0.0 and 1.0');
        }

        $transformed = [];
        $mappedPosition = 0;

        foreach (StringUtils::splitChars($text) as $char) {
            $replacement = $mapping[mb_strtolower($char, 'UTF-8')] ?? null;
            if ($replacement === null) {
                $transformed[] = $char;
                continue;
            }

            if ($density < 1.0) {
                $mappedPosition++;
                $score = (($mappedPosition * 131) + (mb_ord(mb_strtolower($char, 'UTF-8'), 'UTF-8') * 17)) % 100;
                if ($score >= (int) ($density * 100)) {
                    $transformed[] = $char;
                    continue;
                }
            }

            if (CaseHelper::isUpper($char) && self::isAllAlpha($replacement)) {
                $transformed[] = mb_strtoupper($replacement, 'UTF-8');
            } elseif (CaseHelper::isLower($char) && self::isAllAlpha($replacement)) {
                $transformed[] = mb_strtolower($replacement, 'UTF-8');
            } else {
                $transformed[] = $replacement;
            }
        }

        return implode('', $transformed);
    }

    public static function looksLikeLeet(string $text): bool
    {
        preg_match_all('/[^\W_]+/u', $text, $matches);
        $tokens = $matches[0] ?? [];
        $signalSet = array_flip(self::LEET_SIGNAL_CHARS);

        foreach ($tokens as $token) {
            $hasLetter = preg_match('/\p{L}/u', $token) === 1;
            $hasSignal = false;
            foreach (StringUtils::splitChars($token) as $char) {
                if (preg_match('/\d/u', $char) === 1 || isset($signalSet[$char])) {
                    $hasSignal = true;
                    break;
                }
            }

            if ($hasLetter && $hasSignal) {
                return true;
            }
        }

        $lowered = mb_strtolower($text, 'UTF-8');
        return str_contains($lowered, 'n73')
            || str_contains($lowered, 'nj3')
            || str_contains($lowered, '00')
            || str_contains($lowered, 'z4');
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function leetProfiles(): array
    {
        return [
            'basic' => self::BASIC_LEET_PROFILE,
            'readable' => self::READABLE_FULL_PROFILE,
            'full' => self::buildFullLeetProfile(),
        ];
    }

    private static function isAllAlpha(string $text): bool
    {
        return $text !== '' && preg_match('/^\p{L}+$/u', $text) === 1;
    }
}
