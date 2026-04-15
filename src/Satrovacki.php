<?php

declare(strict_types=1);

namespace Skrit;

use Skrit\Core\CaseHelper;
use Skrit\Core\StringUtils;
use Skrit\Core\Tokenizer;
use Skrit\Core\Transliteration;

class Satrovacki
{
    public function __construct(
        protected string $vowels = 'aeiou',
        protected int $minWordLength = 3,
        protected array $exceptions = [],
        protected bool $softTjToCyrillic = false,
        protected string $plainCTarget = 'ц',
    ) {
        Transliteration::assertPlainCTarget($this->plainCTarget);
    }

    public function encode(string $text): string
    {
        $parts = Tokenizer::splitWordOrOther($text);
        $encoded = [];

        foreach ($parts as $part) {
            if (Tokenizer::isAlpha($part)) {
                $encoded[] = $this->encodeWord($part);
            } else {
                $encoded[] = $part;
            }
        }

        return implode('', $encoded);
    }

    public function encodeWord(string $word): string
    {
        if (StringUtils::length($word) < $this->minWordLength) {
            return $word;
        }

        $outputScriptIsCyrillic = Transliteration::containsCyrillic($word);
        $normalizedLatin = $outputScriptIsCyrillic ? Transliteration::cyrillicToLatin($word) : $word;
        $lowerWord = mb_strtolower($normalizedLatin, 'UTF-8');

        $replaced = $this->exceptions[$lowerWord] ?? null;
        if ($replaced === null) {
            $splitIndex = $this->_findSplitIndex($lowerWord);
            $length = StringUtils::length($lowerWord);
            if ($splitIndex <= 0 || $splitIndex >= $length) {
                $replaced = $lowerWord;
            } else {
                $replaced = StringUtils::substr($lowerWord, $splitIndex) . StringUtils::substr($lowerWord, 0, $splitIndex);
            }
        }

        if ($outputScriptIsCyrillic) {
            $replaced = Transliteration::latinToCyrillic(
                $replaced,
                useTjForC: $this->softTjToCyrillic,
                plainCTarget: $this->plainCTarget
            );
        }

        return $this->_applyCase($word, $replaced);
    }

    public function decode(string $text): string
    {
        $parts = Tokenizer::splitWordOrOther($text);
        $decoded = [];

        foreach ($parts as $part) {
            if (Tokenizer::isAlpha($part)) {
                $decoded[] = $this->decodeWord($part);
            } else {
                $decoded[] = $part;
            }
        }

        return implode('', $decoded);
    }

    public function decodeWord(string $word): string
    {
        if (StringUtils::length($word) < $this->minWordLength) {
            return $word;
        }

        $outputScriptIsCyrillic = Transliteration::containsCyrillic($word);
        $normalizedLatin = $outputScriptIsCyrillic ? Transliteration::cyrillicToLatin($word) : $word;
        $lowerWord = mb_strtolower($normalizedLatin, 'UTF-8');

        $reverseExceptions = array_flip($this->exceptions);
        $replaced = $reverseExceptions[$lowerWord] ?? null;

        if ($replaced === null) {
            $candidates = $this->_decodeCandidates($lowerWord);
            if ($candidates !== []) {
                $replaced = $this->_pickBestDecodeCandidate($candidates);
            } else {
                $replaced = $lowerWord;
            }
        }

        if ($outputScriptIsCyrillic) {
            $replaced = Transliteration::latinToCyrillic(
                $replaced,
                useTjForC: $this->softTjToCyrillic,
                plainCTarget: $this->plainCTarget
            );
        }

        return $this->_applyCase($word, $replaced);
    }

    public function canDecodeWord(string $word): bool
    {
        if (StringUtils::length($word) < $this->minWordLength) {
            return false;
        }

        $normalizedLatin = Transliteration::containsCyrillic($word) ? Transliteration::cyrillicToLatin($word) : $word;
        $lowerWord = mb_strtolower($normalizedLatin, 'UTF-8');
        $reverseExceptions = array_flip($this->exceptions);

        if (isset($reverseExceptions[$lowerWord])) {
            return true;
        }

        return $this->_decodeCandidates($lowerWord) !== [];
    }

    public function _encodeLatinWord(string $lowerWord): string
    {
        $replaced = $this->exceptions[$lowerWord] ?? null;
        if ($replaced !== null) {
            return $replaced;
        }

        return $this->_encodeLatinWordPlain($lowerWord);
    }

    public function _encodeLatinWordPlain(string $lowerWord): string
    {
        $splitIndex = $this->_findSplitIndex($lowerWord);
        $length = StringUtils::length($lowerWord);

        if ($splitIndex <= 0 || $splitIndex >= $length) {
            return $lowerWord;
        }

        return StringUtils::substr($lowerWord, $splitIndex) . StringUtils::substr($lowerWord, 0, $splitIndex);
    }

    /**
     * @return list<array{0:int,1:string}>
     */
    public function _decodeCandidates(string $lowerWord): array
    {
        $candidates = [];
        $length = StringUtils::length($lowerWord);

        for ($splitIndex = 1; $splitIndex < $length; $splitIndex++) {
            $candidate = StringUtils::substr($lowerWord, -$splitIndex) . StringUtils::substr($lowerWord, 0, $length - $splitIndex);
            $encodedWithExceptions = $this->_encodeLatinWord($candidate);
            $encodedPlain = $this->_encodeLatinWordPlain($candidate);

            if ($encodedWithExceptions === $lowerWord || $encodedPlain === $lowerWord) {
                $candidates[] = [$splitIndex, $candidate];
            }
        }

        return $candidates;
    }

    /**
     * @param list<array{0:int,1:string}> $candidates
     */
    public function _pickBestDecodeCandidate(array $candidates): string
    {
        $half = StringUtils::length($candidates[0][1]) / 2.0;
        $bestCandidate = $candidates[0];
        $bestScore = $this->scoreCandidate($bestCandidate, $half);

        foreach ($candidates as $candidate) {
            $score = $this->scoreCandidate($candidate, $half);
            if ($this->scoreLessThan($score, $bestScore)) {
                $bestScore = $score;
                $bestCandidate = $candidate;
            }
        }

        return $bestCandidate[1];
    }

    public function _findSplitIndex(string $word): int
    {
        $length = StringUtils::length($word);
        $seenConsonant = false;
        $initialVowelEnd = 0;

        for ($index = 0; $index < $length; $index++) {
            if (!$this->_isVowelAt($word, $index)) {
                $seenConsonant = true;
                continue;
            }

            if ($seenConsonant) {
                $splitIndex = $index + 1;
                while ($splitIndex < $length && $this->_isVowelAt($word, $splitIndex)) {
                    $splitIndex++;
                }

                if ($splitIndex < $length) {
                    return $splitIndex;
                }

                return intdiv($length, 2);
            }

            $initialVowelEnd = $index + 1;
        }

        if ($initialVowelEnd > 0) {
            return $initialVowelEnd;
        }

        return intdiv($length, 2);
    }

    public function _isVowelAt(string $word, int $index): bool
    {
        $length = StringUtils::length($word);
        if ($index < 0 || $index >= $length) {
            return false;
        }

        $vowels = mb_strtolower($this->vowels, 'UTF-8');
        $current = StringUtils::substr($word, $index, 1);

        if (mb_strpos($vowels, $current, 0, 'UTF-8') !== false) {
            return true;
        }

        if ($current !== 'r' || $index === 0 || $index === $length - 1) {
            return false;
        }

        $prev = StringUtils::substr($word, $index - 1, 1);
        $next = StringUtils::substr($word, $index + 1, 1);

        return mb_strpos($vowels, $prev, 0, 'UTF-8') === false && mb_strpos($vowels, $next, 0, 'UTF-8') === false;
    }

    public function _applyCase(string $original, string $transformed): string
    {
        return CaseHelper::applyCase($original, $transformed);
    }

    /**
     * @param array{0:int,1:string} $item
     * @return array{0:int,1:float,2:int,3:int}
     */
    private function scoreCandidate(array $item, float $half): array
    {
        [$splitIndex, $candidate] = $item;
        $secondIsVowel = (StringUtils::length($candidate) > 1 && $this->_isVowelAt($candidate, 1)) ? 1 : 0;
        $startsWithConsonant = ($candidate !== '' && !$this->_isVowelAt($candidate, 0)) ? 1 : 0;

        return [
            -$startsWithConsonant,
            abs($splitIndex - $half),
            -$secondIsVowel,
            $splitIndex,
        ];
    }

    /**
     * @param array{0:int,1:float,2:int,3:int} $left
     * @param array{0:int,1:float,2:int,3:int} $right
     */
    private function scoreLessThan(array $left, array $right): bool
    {
        for ($i = 0; $i < 4; $i++) {
            if ($left[$i] < $right[$i]) {
                return true;
            }
            if ($left[$i] > $right[$i]) {
                return false;
            }
        }

        return false;
    }
}
