<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Skrit\Skrit;

final class SkritTest extends TestCase
{
    public function testDetectMode(): void
    {
        $this->assertSame('satro', Skrit::detectMode('Zemun zakon matori'));
        $this->assertSame('utro', Skrit::detectMode('uzenzabanje'));
        $this->assertSame('leet', Skrit::detectMode('M00n23 k0n24 70r1m4'));
    }

    public function testAutoRoutesToSatro(): void
    {
        [$encoded, $mode] = Skrit::encodeText('Zemun zakon matori', mode: 'auto');
        $this->assertSame('satro', $mode);
        $this->assertSame('Munze konza torima', $encoded);
    }

    public function testAutoDecodesSatroInput(): void
    {
        [$decoded, $mode] = Skrit::encodeText('munze konza', mode: 'auto');
        $this->assertSame('satro', $mode);
        $this->assertSame('zemun zakon', $decoded);

        [$decodedTorima, $modeTorima] = Skrit::encodeText('munze konza torima', mode: 'auto');
        $this->assertSame('satro', $modeTorima);
        $this->assertSame('zemun zakon matori', $decodedTorima);
    }

    public function testAutoEncodesAmbiguousPlainWord(): void
    {
        [$encoded, $mode] = Skrit::encodeText('marija', mode: 'auto');
        $this->assertSame('satro', $mode);
        $this->assertSame('rijama', $encoded);
    }

    public function testAutoDecodesUtroInput(): void
    {
        [$decoded, $mode] = Skrit::encodeText('uzenzabanje', mode: 'auto');
        $this->assertSame('utro', $mode);
        $this->assertSame('bazen', $decoded);
    }

    public function testAutoRoutesToLeet(): void
    {
        [$encoded, $mode] = Skrit::encodeText('Zemun zakon matori', mode: 'auto', detectFrom: 'M00n23');
        $this->assertSame('leet', $mode);
        $this->assertSame('M00n23 k0n24 70r1m4', $encoded);
    }

    public function testAutoRoutesToUtro(): void
    {
        [$encoded, $mode] = Skrit::encodeText('bazen', mode: 'auto', detectFrom: 'uzenzabanje');
        $this->assertSame('utro', $mode);
        $this->assertSame('uzenzabanje', $encoded);
    }

    public function testExplicitModes(): void
    {
        [$satro, $satroMode] = Skrit::encodeText('bazen', mode: 'satro');
        [$utro, $utroMode] = Skrit::encodeText('bazen', mode: 'utro');
        [$leet, $leetMode] = Skrit::encodeText('bazen', mode: 'leet', leetBase: 'satro');

        $this->assertSame('satro', $satroMode);
        $this->assertSame('utro', $utroMode);
        $this->assertSame('leet', $leetMode);
        $this->assertSame('zenba', $satro);
        $this->assertSame('uzenzabanje', $utro);
        $this->assertSame('23n84', $leet);
    }

    public function testLeetFullComplexityPassthrough(): void
    {
        [$encoded, $mode] = Skrit::encodeText(
            'a',
            mode: 'leet',
            leetBase: 'satro',
            minWordLength: 1,
            leetProfile: 'full',
            leetComplexity: 1
        );
        $this->assertSame('leet', $mode);
        $this->assertSame('/\\', $encoded);
    }

    public function testSatroEncodedDetectorWithNoWords(): void
    {
        $this->assertFalse(Skrit::_looksLikeSatroEncoded('123 !!!'));
        $this->assertFalse(Skrit::_looksLikeSatroEncoded('matori'));
        $this->assertTrue(Skrit::_looksLikeSatroEncoded('munze torima'));
    }

    public function testUtroAndLeetDetectors(): void
    {
        $this->assertTrue(Skrit::_looksLikeUtrovacki('uzenzabanje'));
        $this->assertFalse(Skrit::_looksLikeUtrovacki('bazen'));
        $this->assertTrue(Skrit::_looksLikeLeetrovacki('m00n23 k0n24'));
        $this->assertTrue(Skrit::_looksLikeLeetrovacki('/\\/\\470ri21'));
        $this->assertFalse(Skrit::_looksLikeLeetrovacki('zemun zakon'));
        $this->assertFalse(Skrit::_looksLikeLeetrovacki(''));
        $this->assertTrue(Skrit::_looksLikeLeetrovacki('ab|cd'));
    }

    public function testAutoDecodesLeetInputs(): void
    {
        [$decodedSatro, $modeSatro] = Skrit::encodeText('m00n23 k0n24', mode: 'auto');
        $this->assertSame('leet', $modeSatro);
        $this->assertSame('zemun zakon', $decodedSatro);

        [$decodedUtro, $modeUtro] = Skrit::encodeText('00zen24ban73', mode: 'auto');
        $this->assertSame('leet', $modeUtro);
        $this->assertSame('bazen', $decodedUtro);
    }

    public function testDeleetBasicHelper(): void
    {
        $this->assertSame('munze', Skrit::_deleetTextBasic('m00n23'));
        $this->assertSame('uzenzabanje', Skrit::_deleetTextBasic('00zen24ban73'));
        $this->assertSame('MUNZE', Skrit::_deleetTextBasic('M00N23'));
        $this->assertSame('Nje', Skrit::_deleetTextBasic('Nj3'));
    }

    public function testAutoLeetUtroFallbackReturnsDeleeted(): void
    {
        [$decoded, $mode] = Skrit::encodeText('00n73 24', mode: 'auto');
        $this->assertSame('leet', $mode);
        $this->assertSame('unje za', $decoded);
    }

    public function testDetectLeetBaseAndFallbacks(): void
    {
        $this->assertSame('utro', Skrit::detectLeetBase('00zen24ban73'));
        $this->assertSame('utro', Skrit::detectLeetBase('uzenzabanje plain'));
        $this->assertSame('satro', Skrit::detectLeetBase('Zemun zakon matori'));
        $this->assertSame('satro', Skrit::detectLeetBase('123 !!!'));
    }

    public function testDetectModeNoWordsFallback(): void
    {
        $this->assertSame('satro', Skrit::detectMode('123 !!!'));
        $this->assertSame('utro', Skrit::detectMode('uzenzabanje'));
        $this->assertSame('leet', Skrit::detectMode('m00n23'));
    }

    public function testEncodeTextAutoLeetBaseDetection(): void
    {
        [$encoded, $mode] = Skrit::encodeText(
            'Zemun zakon matori',
            mode: 'leet',
            leetBase: 'auto',
            detectFrom: '00zen24ban73'
        );
        $this->assertSame('leet', $mode);
        $this->assertNotSame('', $encoded);
    }

    public function testSatroEncodedDetectorNoChangedPairs(): void
    {
        $this->assertFalse(Skrit::_looksLikeSatroEncoded('aaa'));
    }
}
