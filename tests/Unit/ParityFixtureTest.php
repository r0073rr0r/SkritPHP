<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Skrit\Leet;
use Skrit\Skrit;

final class ParityFixtureTest extends TestCase
{
    public function testParityCasesFromFixture(): void
    {
        $fixturePath = __DIR__ . '/../Fixtures/parity_cases.json';
        $decoded = json_decode((string) file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);
        $cases = $decoded['cases'] ?? [];

        foreach ($cases as $case) {
            $description = (string) ($case['description'] ?? 'fixture-case');
            $input = (string) ($case['input'] ?? '');
            $options = $case['options'] ?? [];
            $expectedText = (string) ($case['expected_text'] ?? '');
            $expectedMode = (string) ($case['expected_mode'] ?? '');

            [$text, $mode] = $this->runEncode($input, $options);

            $this->assertSame($expectedMode, $mode, "{$description} (mode)");
            $this->assertSame($expectedText, $text, "{$description} (text)");
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array{0:string,1:string}
     */
    private function runEncode(string $text, array $options): array
    {
        return Skrit::encodeText(
            text: $text,
            mode: (string) ($options['mode'] ?? 'auto'),
            detectFrom: isset($options['detect_from']) ? (string) $options['detect_from'] : null,
            minWordLength: (int) ($options['min_word_length'] ?? 3),
            plainCTarget: (string) ($options['plain_c_target'] ?? 'ц'),
            softTjToCyrillic: (bool) ($options['soft_tj_to_cyrillic'] ?? false),
            leetBase: (string) ($options['leet_base'] ?? 'auto'),
            leetProfile: (string) ($options['leet_profile'] ?? 'basic'),
            leetComplexity: (int) ($options['leet_complexity'] ?? 0),
            leetDensity: (float) ($options['leet_density'] ?? Leet::DEFAULT_LEET_DENSITY),
            zaStyle: (string) ($options['za_style'] ?? '24'),
            njeStyle: (string) ($options['nje_style'] ?? 'n73'),
            utroPrefix: (string) ($options['utro_prefix'] ?? 'u'),
            utroInfix: (string) ($options['utro_infix'] ?? 'za'),
            utroSuffix: (string) ($options['utro_suffix'] ?? 'nje'),
        );
    }
}
