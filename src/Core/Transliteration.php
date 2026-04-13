<?php

declare(strict_types=1);

namespace Skrit\Core;

use InvalidArgumentException;

final class Transliteration
{
    /** @var array<string, string> */
    public const CYR_TO_LAT = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'ђ' => 'đ',
        'е' => 'e',
        'ж' => 'ž',
        'з' => 'z',
        'и' => 'i',
        'ј' => 'j',
        'к' => 'k',
        'л' => 'l',
        'љ' => 'lj',
        'м' => 'm',
        'н' => 'n',
        'њ' => 'nj',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'ћ' => 'ć',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'č',
        'џ' => 'dž',
        'ш' => 'š',
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Ђ' => 'Đ',
        'Е' => 'E',
        'Ж' => 'Ž',
        'З' => 'Z',
        'И' => 'I',
        'Ј' => 'J',
        'К' => 'K',
        'Л' => 'L',
        'Љ' => 'Lj',
        'М' => 'M',
        'Н' => 'N',
        'Њ' => 'Nj',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'Ћ' => 'Ć',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'C',
        'Ч' => 'Č',
        'Џ' => 'Dž',
        'Ш' => 'Š',
    ];

    /** @var array<string, string> */
    public const LAT_TO_CYR_DIGRAPHS = [
        'dž' => 'џ',
        'Dž' => 'Џ',
        'DŽ' => 'Џ',
        'lj' => 'љ',
        'Lj' => 'Љ',
        'LJ' => 'Љ',
        'nj' => 'њ',
        'Nj' => 'Њ',
        'NJ' => 'Њ',
    ];

    /** @var array<string, string> */
    public const LAT_TO_CYR_SINGLE = [
        'a' => 'а',
        'b' => 'б',
        'v' => 'в',
        'g' => 'г',
        'd' => 'д',
        'đ' => 'ђ',
        'e' => 'е',
        'ž' => 'ж',
        'z' => 'з',
        'i' => 'и',
        'j' => 'ј',
        'k' => 'к',
        'l' => 'л',
        'm' => 'м',
        'n' => 'н',
        'o' => 'о',
        'p' => 'п',
        'r' => 'р',
        's' => 'с',
        't' => 'т',
        'ć' => 'ћ',
        'u' => 'у',
        'f' => 'ф',
        'h' => 'х',
        'c' => 'ц',
        'č' => 'ч',
        'š' => 'ш',
        'A' => 'А',
        'B' => 'Б',
        'V' => 'В',
        'G' => 'Г',
        'D' => 'Д',
        'Đ' => 'Ђ',
        'E' => 'Е',
        'Ž' => 'Ж',
        'Z' => 'З',
        'I' => 'И',
        'J' => 'Ј',
        'K' => 'К',
        'L' => 'Л',
        'M' => 'М',
        'N' => 'Н',
        'O' => 'О',
        'P' => 'П',
        'R' => 'Р',
        'S' => 'С',
        'T' => 'Т',
        'Ć' => 'Ћ',
        'U' => 'У',
        'F' => 'Ф',
        'H' => 'Х',
        'C' => 'Ц',
        'Č' => 'Ч',
        'Š' => 'Ш',
    ];

    /** @var array<string, string> */
    private const OPTIONAL_TJ_TO_CYR = [
        'tj' => 'ћ',
        'Tj' => 'Ћ',
        'TJ' => 'Ћ',
    ];

    public static function isCyrillicChar(string $char): bool
    {
        return preg_match('/[\x{0400}-\x{052F}]/u', $char) === 1;
    }

    public static function containsCyrillic(string $text): bool
    {
        return preg_match('/[\x{0400}-\x{052F}]/u', $text) === 1;
    }

    public static function cyrillicToLatin(string $text): string
    {
        $chars = StringUtils::splitChars($text);
        $converted = [];

        foreach ($chars as $char) {
            $converted[] = self::CYR_TO_LAT[$char] ?? $char;
        }

        return implode('', $converted);
    }

    public static function latinToCyrillic(
        string $text,
        bool $useTjForC = false,
        string $plainCTarget = 'ц'
    ): string {
        self::assertPlainCTarget($plainCTarget);

        $lowerCMap = ['ц' => 'ц', 'ч' => 'ч', 'ћ' => 'ћ'];
        $upperCMap = ['ц' => 'Ц', 'ч' => 'Ч', 'ћ' => 'Ћ'];
        $mappedCLower = $lowerCMap[$plainCTarget];
        $mappedCUpper = $upperCMap[$plainCTarget];

        $converted = [];
        $index = 0;
        $length = StringUtils::length($text);

        while ($index < $length) {
            $first = StringUtils::substr($text, $index, 1);
            $second = $index + 1 < $length ? StringUtils::substr($text, $index + 1, 1) : '';
            $twoChars = $first . $second;

            if ($useTjForC && isset(self::OPTIONAL_TJ_TO_CYR[$twoChars])) {
                $converted[] = self::OPTIONAL_TJ_TO_CYR[$twoChars];
                $index += 2;
                continue;
            }

            if (isset(self::LAT_TO_CYR_DIGRAPHS[$twoChars])) {
                $converted[] = self::LAT_TO_CYR_DIGRAPHS[$twoChars];
                $index += 2;
                continue;
            }

            if ($first === 'c') {
                $converted[] = $mappedCLower;
            } elseif ($first === 'C') {
                $converted[] = $mappedCUpper;
            } else {
                $converted[] = self::LAT_TO_CYR_SINGLE[$first] ?? $first;
            }

            $index++;
        }

        return implode('', $converted);
    }

    public static function assertPlainCTarget(string $plainCTarget): void
    {
        if (!in_array($plainCTarget, ['ц', 'ч', 'ћ'], true)) {
            throw new InvalidArgumentException("plain_c_target must be one of: 'ц', 'ч', 'ћ'");
        }
    }
}
