<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Skrit\Utrovacki;

final class UtrovackiTest extends TestCase
{
    private Utrovacki $encoder;

    protected function setUp(): void
    {
        $this->encoder = new Utrovacki();
    }

    public function testUserExamplesLatin(): void
    {
        $this->assertSame('ushtoljzapinje', $this->encoder->encode('pishtolj'));
        $this->assertSame('uzenzabanje', $this->encoder->encode('bazen'));
        $this->assertSame('učkazamanje', $this->encoder->encode('mačka'));
        $this->assertSame('učkazaznanje', $this->encoder->encode('značka'));
        $this->assertSame('uvozađanje', $this->encoder->encode('đavo'));
    }

    public function testSentenceAndCase(): void
    {
        $this->assertSame(
            'Ushtoljzapinje uzenzabanje UČKAZAMANJE',
            $this->encoder->encode('Pishtolj bazen MAČKA')
        );
    }

    public function testCyrillicAndMixed(): void
    {
        $this->assertSame('учказамање учказазнање увозађање', $this->encoder->encode('мачка значка ђаво'));
        $this->assertSame('učkazamanje учказазнање uvozađanje', $this->encoder->encode('mačka значка đavo'));
    }

    public function testMinWordLength(): void
    {
        $encoder = new Utrovacki(minWordLength: 5);
        $this->assertSame('pas uzenzabanje', $encoder->encode('pas bazen'));
    }

    public function testSoftTjAndPlainCTarget(): void
    {
        $strict = new Utrovacki();
        $soft = new Utrovacki(softTjToCyrillic: true);

        $this->assertSame('утјбзаање', $strict->encode('атјб'));
        $this->assertSame('ућбзаање', $soft->encode('атјб'));

        $this->assertSame('уцбзаање', (new Utrovacki(plainCTarget: 'ц'))->encode('ацб'));
        $this->assertSame('учбзаање', (new Utrovacki(plainCTarget: 'ч'))->encode('ацб'));
        $this->assertSame('ућбзаање', (new Utrovacki(plainCTarget: 'ћ'))->encode('ацб'));
    }

    public function testExceptionsAreSupported(): void
    {
        $withExceptions = new Utrovacki(exceptions: ['brate' => 'tebra']);
        $this->assertSame('utezabranje', $withExceptions->encode('brate'));
    }

    public function testCustomAffixes(): void
    {
        $custom = new Utrovacki(prefix: 'x', infix: 'yy', suffix: 'zz');
        $this->assertSame('xzenyybazz', $custom->encode('bazen'));
    }

    public function testDecodeExamples(): void
    {
        $this->assertSame('bazen', $this->encoder->decode('uzenzabanje'));
        $this->assertSame('Bazen', $this->encoder->decode('Uzenzabanje'));
        $this->assertSame('базен', $this->encoder->decode('узензабање'));
        $this->assertSame('bazen', $this->encoder->decode('bazen'));
    }

    public function testDecodeHandlesAmbiguousInfixOccurrences(): void
    {
        $this->assertSame('zakon', $this->encoder->decode('ukonzazanje'));
        $this->assertSame('zakonodavac', $this->encoder->decode('ukonodavaczazanje'));
        $this->assertSame('baza', $this->encoder->decode('uzazabanje'));
        $this->assertSame('oaza', $this->encoder->decode('uzazaoanje'));
    }

    public function testCanDecodeWord(): void
    {
        $this->assertTrue($this->encoder->canDecodeWord('uzenzabanje'));
        $this->assertTrue($this->encoder->canDecodeWord('узензабање'));
        $this->assertFalse($this->encoder->canDecodeWord('bazen'));
    }

    public function testDecodeShortAndNonWordParts(): void
    {
        $this->assertSame('ab', $this->encoder->decodeWord('ab'));
        $this->assertSame('bazen, test!', $this->encoder->decode('uzenzabanje, test!'));
    }

    public function testSplitEncodedPartsEdgeCases(): void
    {
        $this->assertNull($this->encoder->_splitEncodedParts('bazen'));
        $this->assertNull($this->encoder->_splitEncodedParts('uzenzaban'));
        $this->assertNull($this->encoder->_splitEncodedParts('u'));
        $this->assertNull($this->encoder->_splitEncodedParts('unje'));
        $this->assertNull($this->encoder->_splitEncodedParts('uxxxnje'));
        $this->assertNull($this->encoder->_splitEncodedParts('uzanje'));
        $this->assertFalse($this->encoder->canDecodeWord('ab'));
    }

    public function testExceptionLengthMismatchAndSplitFallbackBranches(): void
    {
        $mismatch = new Utrovacki(exceptions: ['brate' => 'tb']);
        $this->assertSame('utbzanje', $mismatch->encodeWord('brate'));

        $fallback = new class () extends Utrovacki {
            public function _findSplitIndex(string $word): int
            {
                return 0;
            }
        };
        $this->assertSame('ubazenzanje', $fallback->encodeWord('bazen'));
    }
}
