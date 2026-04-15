<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Skrit\Leetrovacki;
use Skrit\Satrovacki;
use Skrit\Utrovacki;

final class CorpusRoundtripTest extends TestCase
{
    private const MIN_WORD_LENGTH = 3;

    private Satrovacki $satro;
    private Utrovacki $utro;
    private Leetrovacki $leetSatro;
    private Leetrovacki $leetUtro;

    protected function setUp(): void
    {
        $this->satro = new Satrovacki();
        $this->utro = new Utrovacki();
        $this->leetSatro = new Leetrovacki(baseMode: 'satro', leetDensity: 1.0);
        $this->leetUtro = new Leetrovacki(baseMode: 'utro', leetDensity: 1.0);
    }

    public function testSatroNoCrash(): void
    {
        foreach (self::corpus() as $word) {
            $this->satro->encode($word);
        }

        $this->addToAssertionCount(1);
    }

    public function testSatroLengthPreserved(): void
    {
        foreach (self::corpus() as $word) {
            $encoded = $this->satro->encodeWord($word);
            $this->assertSame(
                mb_strlen($word, 'UTF-8'),
                mb_strlen($encoded, 'UTF-8'),
                "Length mismatch for word {$word}"
            );
        }
    }

    public function testSatroRoundtripConsistency(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $lower = mb_strtolower($word, 'UTF-8');
            $encoded = $this->satro->encodeWord($lower);
            $decoded = $this->satro->decodeWord($encoded);
            $reEncoded = $this->satro->encodeWord($decoded);

            $this->assertSame(
                $encoded,
                $reEncoded,
                "Satro consistency failed: {$word} -> {$encoded} -> {$decoded} -> {$reEncoded}"
            );
        }
    }

    public function testSatroShortWordsUnchanged(): void
    {
        $assertions = 0;
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') >= self::MIN_WORD_LENGTH) {
                continue;
            }

            $this->assertSame($word, $this->satro->encodeWord($word), "Short word changed: {$word}");
            $assertions++;
        }

        if ($assertions === 0) {
            $this->addToAssertionCount(1);
        }
    }

    public function testSatroTitleCasePreserved(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $titled = mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($word, 1, null, 'UTF-8');
            $encoded = $this->satro->encodeWord($titled);
            $first = mb_substr($encoded, 0, 1, 'UTF-8');
            $this->assertSame(mb_strtoupper($first, 'UTF-8'), $first, "Titlecase not preserved for {$word}");
        }
    }

    public function testUtroNoCrash(): void
    {
        foreach (self::corpus() as $word) {
            $this->utro->encode($word);
        }

        $this->addToAssertionCount(1);
    }

    public function testUtroOutputLongerThanInput(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $encoded = $this->utro->encodeWord($word);
            $this->assertGreaterThan(
                mb_strlen($word, 'UTF-8'),
                mb_strlen($encoded, 'UTF-8'),
                "Utro output is not longer for {$word}"
            );
        }
    }

    public function testUtroStartsWithPrefix(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $encoded = mb_strtolower($this->utro->encodeWord($word), 'UTF-8');
            $this->assertTrue(
                str_starts_with($encoded, 'u') || str_starts_with($encoded, 'у'),
                "Utro output does not start with prefix for {$word}"
            );
        }
    }

    public function testUtroContainsInfix(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $encoded = mb_strtolower($this->utro->encodeWord($word), 'UTF-8');
            $this->assertTrue(
                str_contains($encoded, 'za') || str_contains($encoded, 'за'),
                "Utro output does not contain infix for {$word}"
            );
        }
    }

    public function testUtroEndsWithSuffix(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $encoded = mb_strtolower($this->utro->encodeWord($word), 'UTF-8');
            $this->assertTrue(
                str_ends_with($encoded, 'nje') || str_ends_with($encoded, 'ње'),
                "Utro output does not end with suffix for {$word}"
            );
        }
    }

    public function testUtroStrictRoundtrip(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $lower = mb_strtolower($word, 'UTF-8');
            $encoded = $this->utro->encodeWord($lower);
            $decoded = $this->utro->decodeWord($encoded);

            $this->assertSame($lower, $decoded, "Utro roundtrip failed for {$word}: {$encoded} -> {$decoded}");
        }
    }

    public function testUtroShortWordsUnchanged(): void
    {
        $assertions = 0;
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') >= self::MIN_WORD_LENGTH) {
                continue;
            }

            $this->assertSame($word, $this->utro->encodeWord($word), "Short utro word changed: {$word}");
            $assertions++;
        }

        if ($assertions === 0) {
            $this->addToAssertionCount(1);
        }
    }

    public function testLeetNoCrashInBothModes(): void
    {
        foreach (self::corpus() as $word) {
            $this->leetSatro->encode($word);
            $this->leetUtro->encode($word);
        }

        $this->addToAssertionCount(1);
    }

    public function testLeetSatroContainsLeetCharactersWhenTriggered(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $lower = mb_strtolower($word, 'UTF-8');
            if (preg_match('/[aeioustz]/u', $lower) !== 1) {
                continue;
            }

            $encoded = $this->leetSatro->encodeWord($lower);
            $this->assertSame(
                1,
                preg_match('/[4310572086]/u', $encoded),
                "Leet satro output has no leet signal for {$word}: {$encoded}"
            );
        }
    }

    public function testLeetUtroStartsWith00(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $encoded = $this->leetUtro->encodeWord(mb_strtolower($word, 'UTF-8'));
            $this->assertTrue(str_starts_with($encoded, '00'), "Leet utro output does not start with 00 for {$word}");
        }
    }

    public function testLeetUtroContains24(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $encoded = $this->leetUtro->encodeWord(mb_strtolower($word, 'UTF-8'));
            $this->assertStringContainsString('24', $encoded, "Leet utro output has no 24 for {$word}");
        }
    }

    public function testLeetUtroEndsWithN73(): void
    {
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') < self::MIN_WORD_LENGTH) {
                continue;
            }

            $encoded = $this->leetUtro->encodeWord(mb_strtolower($word, 'UTF-8'));
            $this->assertTrue(str_ends_with($encoded, 'n73'), "Leet utro output does not end with n73 for {$word}");
        }
    }

    public function testLeetShortWordsUnchangedInBothModes(): void
    {
        $assertions = 0;
        foreach (self::corpus() as $word) {
            if (mb_strlen($word, 'UTF-8') >= self::MIN_WORD_LENGTH) {
                continue;
            }

            $this->assertSame($word, $this->leetSatro->encodeWord($word), "Short leet satro word changed: {$word}");
            $this->assertSame($word, $this->leetUtro->encodeWord($word), "Short leet utro word changed: {$word}");
            $assertions += 2;
        }

        if ($assertions === 0) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @return list<string>
     */
    private static function corpus(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $fixturePath = __DIR__ . '/../Fixtures/corpus_963.json';
        $decoded = json_decode((string) file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);
        $corpus = $decoded['corpus_963'] ?? [];

        return $cached = array_values(array_map(static fn (mixed $word): string => (string) $word, $corpus));
    }
}
