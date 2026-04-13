<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Skrit\Leetrovacki;

final class LeetrovackiTest extends TestCase
{
    public function testAutoDetectsUtroPattern(): void
    {
        $encoder = new Leetrovacki(leetDensity: 1.0);
        $this->assertSame('00čka24man73', $encoder->encode('učkazamanje'));
        $this->assertSame('00zen24ban73', $encoder->encode('uzenzabanje'));
    }

    public function testDefaultDensityIs86Percent(): void
    {
        $encoder = new Leetrovacki(baseMode: 'satro');
        $this->assertSame('s7pr', $encoder->encode('prst'));
    }

    public function testAutoFallsBackToSatroLeet(): void
    {
        $encoder = new Leetrovacki(leetDensity: 1.0);
        $this->assertSame('m00n23', $encoder->encode('munze'));
        $this->assertSame('м00н23', $encoder->encode('мунзе'));
        $this->assertSame('00shtolj24pin73', $encoder->encode('ushtoljzapinje'));
    }

    public function testExplicitUtroModeFromPlain(): void
    {
        $encoder = new Leetrovacki(baseMode: 'utro', leetDensity: 1.0);
        $this->assertSame('00čka24man73', $encoder->encode('mačka'));
        $this->assertSame('00vo24đan73', $encoder->encode('đavo'));
    }

    public function testExplicitSatroModeFromPlain(): void
    {
        $encoder = new Leetrovacki(baseMode: 'satro', leetDensity: 1.0);
        $this->assertSame('m00n23', $encoder->encode('zemun'));
        $this->assertSame('23nb4', $encoder->encode('bazen'));
    }

    public function testAllSatroModuleExamplesInSatroMode(): void
    {
        $encoder = new Leetrovacki(baseMode: 'satro', leetDensity: 1.0);
        $cases = [
            'Beograd' => 'Gr4db30',
            'Zemun zakon matori' => 'M00n23 k0n24 70r1m4',
            'riba ribi grize rep' => 'b4r1 b1r1 23gr1 pr3',
            'Земун закон матори' => 'М00н23 к0н24 70р1м4',
            'Zemun закон matori' => 'M00n23 к0н24 70r1m4',
            'brate BRATE Brate' => '73br4 73BR4 73br4',
            '123, ajde!' => '123, jd34!',
            'prst' => '57pr',
            'прст' => '57пр',
        ];

        foreach ($cases as $source => $expected) {
            $this->assertSame($expected, $encoder->encode($source));
        }

        $this->assertSame(
            'riba rep',
            (new Leetrovacki(baseMode: 'satro', minWordLength: 5, leetDensity: 1.0))->encode('riba rep')
        );
    }

    public function testUtroStyles(): void
    {
        $encoder = new Leetrovacki(baseMode: 'utro', zaStyle: 'z4', njeStyle: 'nj3', leetDensity: 1.0);
        $this->assertSame('00zenz4banj3', $encoder->encode('bazen'));
    }

    public function testAllUtroModuleExamplesInUtroMode(): void
    {
        $encoder = new Leetrovacki(baseMode: 'utro', leetDensity: 1.0);
        $cases = [
            'pishtolj' => '00shtolj24pin73',
            'bazen' => '00zen24ban73',
            'mačka' => '00čka24man73',
            'značka' => '00čka24znan73',
            'đavo' => '00vo24đan73',
            'Pishtolj bazen MAČKA' => '00shtolj24pin73 00zen24ban73 00ČKA24MAN73',
            'мачка значка ђаво' => '00чка24ман73 00чка24знан73 00во24ђан73',
            'mačka значка đavo' => '00čka24man73 00чка24знан73 00vo24đan73',
            'pas bazen' => '00s24pan73 00zen24ban73',
        ];

        foreach ($cases as $source => $expected) {
            $this->assertSame($expected, $encoder->encode($source));
        }

        $withExceptions = new Leetrovacki(baseMode: 'utro', exceptions: ['brate' => 'tebra'], leetDensity: 1.0);
        $this->assertSame('00te24bran73', $withExceptions->encode('brate'));
        $this->assertSame(
            'pas 00zen24ban73',
            (new Leetrovacki(baseMode: 'utro', minWordLength: 5, leetDensity: 1.0))->encode('pas bazen')
        );
    }

    public function testNjeCyrillicOption(): void
    {
        $encoder = new Leetrovacki(baseMode: 'utro', njeStyle: 'њ', leetDensity: 1.0);
        $this->assertSame('00чка24мањ', $encoder->encode('мачка'));
    }

    public function testTjAndPlainCOptionsPropagate(): void
    {
        $encoder = new Leetrovacki(
            baseMode: 'utro',
            softTjToCyrillic: true,
            plainCTarget: 'ћ',
            leetDensity: 1.0
        );
        $this->assertSame('00ћб24ан73', $encoder->encode('атјб'));
        $this->assertSame('00ћб24ан73', $encoder->encode('ацб'));
        $this->assertSame(
            '00чб24ан73',
            (new Leetrovacki(baseMode: 'utro', plainCTarget: 'ч', leetDensity: 1.0))->encode('ацб')
        );
    }

    public function testInvalidOptionsRaise(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Leetrovacki(baseMode: 'bad');
    }

    public function testInvalidZaStyleRaises(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Leetrovacki(zaStyle: 'bad');
    }

    public function testInvalidNjeStyleRaises(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Leetrovacki(njeStyle: 'bad');
    }

    public function testInvalidComplexityRaises(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Leetrovacki(leetComplexity: -1);
    }

    public function testFullProfileComplexityIsUsed(): void
    {
        $encoder = new Leetrovacki(
            baseMode: 'satro',
            minWordLength: 1,
            leetProfile: 'full',
            leetComplexity: 1
        );
        $this->assertSame('/\\', $encoder->encode('a'));
    }

    public function testFullLetterLeetBranchesInUtroHelper(): void
    {
        $encoder = new Leetrovacki(baseMode: 'utro', leetProfile: 'full', leetDensity: 1.0);

        $present = $encoder->_leetifyUtro('uzenzabanje', false);
        $absent = $encoder->_leetifyUtro('matori', false);
        $plain = (new Leetrovacki(baseMode: 'utro'))->_leetifyUtro('matori', false);

        $this->assertSame('0023^/24I34^/,_|3', $present);
        $this->assertSame('/\\/\\470ri21', $absent);
        $this->assertSame('matori', $plain);
    }

    public function testNjeReplacementFalseBranch(): void
    {
        $encoder = new Leetrovacki(njeStyle: 'њ');
        $this->assertSame('nj', $encoder->_njeReplacement(false));
    }

    public function testInvalidLeetProfileRaises(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Leetrovacki(leetProfile: 'missing');
    }

    public function testInvalidLeetDensityRaises(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Leetrovacki(leetDensity: 1.1);
    }
}
