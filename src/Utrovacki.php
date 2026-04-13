<?php

declare(strict_types=1);

namespace Skrit;

use Skrit\Core\StringUtils;
use Skrit\Core\Transliteration;

class Utrovacki extends Satrovacki
{
    public function __construct(
        string $vowels = 'aeiou',
        int $minWordLength = 3,
        array $exceptions = [],
        bool $softTjToCyrillic = false,
        string $plainCTarget = 'ц',
        protected string $prefix = 'u',
        protected string $infix = 'za',
        protected string $suffix = 'nje',
    ) {
        parent::__construct(
            vowels: $vowels,
            minWordLength: $minWordLength,
            exceptions: $exceptions,
            softTjToCyrillic: $softTjToCyrillic,
            plainCTarget: $plainCTarget
        );

        $this->prefix = mb_strtolower($this->prefix, 'UTF-8');
        $this->infix = mb_strtolower($this->infix, 'UTF-8');
        $this->suffix = mb_strtolower($this->suffix, 'UTF-8');
    }

    public function encodeWord(string $word): string
    {
        if (StringUtils::length($word) < $this->minWordLength) {
            return $word;
        }

        $outputScriptIsCyrillic = Transliteration::containsCyrillic($word);
        $normalizedLatin = $outputScriptIsCyrillic ? Transliteration::cyrillicToLatin($word) : $word;
        $lowerWord = mb_strtolower($normalizedLatin, 'UTF-8');
        $splitIndex = $this->_findSplitIndex($lowerWord);

        $firstPart = '';
        $secondPart = '';
        $exceptionValue = $this->exceptions[$lowerWord] ?? null;

        if ($exceptionValue !== null) {
            $satroWord = mb_strtolower($exceptionValue, 'UTF-8');
            $length = StringUtils::length($lowerWord);
            if (
                $splitIndex > 0
                && $splitIndex < $length
                && StringUtils::length($satroWord) === $length
            ) {
                $secondLen = $length - $splitIndex;
                $secondPart = StringUtils::substr($satroWord, 0, $secondLen);
                $firstPart = StringUtils::substr($satroWord, $secondLen);
            } else {
                $secondPart = $satroWord;
            }
        } else {
            $length = StringUtils::length($lowerWord);
            if ($splitIndex <= 0 || $splitIndex >= $length) {
                $secondPart = $lowerWord;
            } else {
                $firstPart = StringUtils::substr($lowerWord, 0, $splitIndex);
                $secondPart = StringUtils::substr($lowerWord, $splitIndex);
            }
        }

        $transformed = $this->prefix . $secondPart . $this->infix . $firstPart . $this->suffix;

        if ($outputScriptIsCyrillic) {
            $transformed = Transliteration::latinToCyrillic(
                $transformed,
                useTjForC: $this->softTjToCyrillic,
                plainCTarget: $this->plainCTarget
            );
        }

        return $this->_applyCase($word, $transformed);
    }

    public function decodeWord(string $word): string
    {
        if (StringUtils::length($word) < $this->minWordLength) {
            return $word;
        }

        $outputScriptIsCyrillic = Transliteration::containsCyrillic($word);
        $normalizedLatin = $outputScriptIsCyrillic ? Transliteration::cyrillicToLatin($word) : $word;
        $lowerWord = mb_strtolower($normalizedLatin, 'UTF-8');

        $parsed = $this->_splitEncodedParts($lowerWord);
        if ($parsed === null) {
            $transformed = $lowerWord;
        } else {
            [$firstPart, $secondPart] = $parsed;
            $transformed = $firstPart . $secondPart;
        }

        if ($outputScriptIsCyrillic) {
            $transformed = Transliteration::latinToCyrillic(
                $transformed,
                useTjForC: $this->softTjToCyrillic,
                plainCTarget: $this->plainCTarget
            );
        }

        return $this->_applyCase($word, $transformed);
    }

    public function canDecodeWord(string $word): bool
    {
        if (StringUtils::length($word) < $this->minWordLength) {
            return false;
        }

        $normalizedLatin = Transliteration::containsCyrillic($word) ? Transliteration::cyrillicToLatin($word) : $word;
        return $this->_splitEncodedParts(mb_strtolower($normalizedLatin, 'UTF-8')) !== null;
    }

    /**
     * @return array{0:string,1:string}|null
     */
    public function _splitEncodedParts(string $lowerWord): ?array
    {
        if (!StringUtils::startsWith($lowerWord, $this->prefix)) {
            return null;
        }

        if ($this->suffix !== '' && !StringUtils::endsWith($lowerWord, $this->suffix)) {
            return null;
        }

        $end = StringUtils::length($lowerWord) - StringUtils::length($this->suffix);
        if ($end <= StringUtils::length($this->prefix)) {
            return null;
        }

        $core = StringUtils::substr($lowerWord, StringUtils::length($this->prefix), $end - StringUtils::length($this->prefix));
        $splitAt = mb_strrpos($core, $this->infix, 0, 'UTF-8');
        if ($splitAt === false) {
            return null;
        }

        $secondPart = StringUtils::substr($core, 0, (int) $splitAt);
        $firstPart = StringUtils::substr($core, (int) $splitAt + StringUtils::length($this->infix));

        if ($secondPart === '' && $firstPart === '') {
            return null;
        }

        return [$firstPart, $secondPart];
    }
}
