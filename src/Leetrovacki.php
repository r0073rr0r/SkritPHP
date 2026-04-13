<?php

declare(strict_types=1);

namespace Skrit;

use InvalidArgumentException;
use Skrit\Core\StringUtils;
use Skrit\Core\Tokenizer;
use Skrit\Core\Transliteration;

class Leetrovacki
{
    private Satrovacki $satro;
    private Utrovacki $utro;

    /** @var array<string, string> */
    private array $resolvedLeetMap;
    private bool $fullLetterLeet;

    /**
     * @param array<string, string> $exceptions
     * @param array<string, string>|null $leetMap
     */
    public function __construct(
        private string $baseMode = 'auto',
        private string $zaStyle = '24',
        private string $njeStyle = 'n73',
        private string $prefixStyle = '00',
        private string $leetProfile = 'basic',
        private int $leetComplexity = 0,
        private float $leetDensity = Leet::DEFAULT_LEET_DENSITY,
        private string $vowels = 'aeiou',
        private int $minWordLength = 3,
        private array $exceptions = [],
        private bool $softTjToCyrillic = false,
        private string $plainCTarget = 'ц',
        private ?array $leetMap = null,
    ) {
        $this->validateOptions();

        $this->satro = new Satrovacki(
            vowels: $this->vowels,
            minWordLength: $this->minWordLength,
            exceptions: $this->exceptions,
            softTjToCyrillic: $this->softTjToCyrillic,
            plainCTarget: $this->plainCTarget
        );

        $this->utro = new Utrovacki(
            vowels: $this->vowels,
            minWordLength: $this->minWordLength,
            exceptions: $this->exceptions,
            softTjToCyrillic: $this->softTjToCyrillic,
            plainCTarget: $this->plainCTarget
        );

        $this->resolvedLeetMap = Leet::getLeetProfile(
            name: $this->leetProfile,
            customMap: $this->leetMap,
            complexity: $this->leetComplexity
        );

        $this->fullLetterLeet = $this->leetMap !== null || in_array($this->leetProfile, ['readable', 'full'], true);
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

        [$variant, $baseWord] = $this->resolveBaseWord($lowerWord);
        if ($variant === 'utro') {
            $transformedLatin = $this->_leetifyUtro($baseWord, $outputScriptIsCyrillic);
        } else {
            $transformedLatin = $this->_leetifySatro($baseWord);
        }

        if ($outputScriptIsCyrillic) {
            $transformed = Transliteration::latinToCyrillic(
                $transformedLatin,
                useTjForC: $this->softTjToCyrillic,
                plainCTarget: $this->plainCTarget
            );
        } else {
            $transformed = $transformedLatin;
        }

        return $this->_applyCase($word, $transformed);
    }

    /**
     * @return array{0:string,1:string}
     */
    public function resolveBaseWord(string $lowerWord): array
    {
        if ($this->baseMode === 'utro') {
            return ['utro', $this->utro->encodeWord($lowerWord)];
        }

        if ($this->baseMode === 'satro') {
            return ['satro', $this->satro->encodeWord($lowerWord)];
        }

        if ($this->_looksLikeUtro($lowerWord)) {
            return ['utro', $lowerWord];
        }

        return ['satro', $lowerWord];
    }

    public function _looksLikeUtro(string $word): bool
    {
        return StringUtils::startsWith($word, 'u') && str_contains($word, 'za') && StringUtils::endsWith($word, 'nje');
    }

    public function _leetifyUtro(string $word, bool $outputScriptIsCyrillic): string
    {
        $transformed = $word;

        if (StringUtils::startsWith($transformed, 'u')) {
            $transformed = $this->prefixStyle . StringUtils::substr($transformed, 1);
        }

        $zaIndex = mb_strpos($transformed, 'za', 0, 'UTF-8');
        if ($zaIndex !== false) {
            $zaIndex = (int) $zaIndex;
            $left = StringUtils::substr($transformed, 0, $zaIndex);
            $right = StringUtils::substr($transformed, $zaIndex + 2);

            if ($this->fullLetterLeet) {
                $left = Leet::applyLeet($left, $this->resolvedLeetMap, density: $this->leetDensity);
                $right = Leet::applyLeet($right, $this->resolvedLeetMap, density: $this->leetDensity);
            }

            $transformed = $left . $this->zaStyle . $right;
        } elseif ($this->fullLetterLeet) {
            $transformed = Leet::applyLeet($transformed, $this->resolvedLeetMap, density: $this->leetDensity);
        }

        if (StringUtils::endsWith($transformed, 'nje')) {
            $suffix = $this->_njeReplacement($outputScriptIsCyrillic);
            $transformed = StringUtils::substr($transformed, 0, StringUtils::length($transformed) - 3) . $suffix;
        }

        return $transformed;
    }

    public function _njeReplacement(bool $outputScriptIsCyrillic): string
    {
        if ($this->njeStyle === 'њ') {
            if ($outputScriptIsCyrillic) {
                return 'nj';
            }
            return 'nj';
        }

        return $this->njeStyle;
    }

    public function _leetifySatro(string $word): string
    {
        return Leet::applyLeet($word, $this->resolvedLeetMap, density: $this->leetDensity);
    }

    public function _applyCase(string $original, string $transformed): string
    {
        return $this->satro->_applyCase($original, $transformed);
    }

    private function validateOptions(): void
    {
        if (!in_array($this->baseMode, ['auto', 'utro', 'satro'], true)) {
            throw new InvalidArgumentException("base_mode must be one of: 'auto', 'utro', 'satro'");
        }

        if (!in_array($this->zaStyle, ['24', 'z4'], true)) {
            throw new InvalidArgumentException("za_style must be one of: '24', 'z4'");
        }

        if (!in_array($this->njeStyle, ['n73', 'nj3', 'њ'], true)) {
            throw new InvalidArgumentException("nje_style must be one of: 'n73', 'nj3', 'њ'");
        }

        Transliteration::assertPlainCTarget($this->plainCTarget);

        if ($this->leetMap === null && !in_array($this->leetProfile, Leet::availableProfiles(), true)) {
            $valid = implode(', ', Leet::availableProfiles());
            throw new InvalidArgumentException("leet_profile must be one of: {$valid}");
        }

        if ($this->leetComplexity < 0) {
            throw new InvalidArgumentException('leet_complexity must be a non-negative integer');
        }

        if ($this->leetDensity < 0.0 || $this->leetDensity > 1.0) {
            throw new InvalidArgumentException('leet_density must be between 0.0 and 1.0');
        }
    }
}
