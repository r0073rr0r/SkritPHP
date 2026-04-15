<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Skrit\Core\Transliteration;
use Skrit\Satrovacki;

final class SatrovackiTest extends TestCase
{
    private Satrovacki $encoder;

    protected function setUp(): void
    {
        $this->encoder = new Satrovacki();
    }

    public function testBasicExamplesLatin(): void
    {
        $this->assertSame('Gradbeo', $this->encoder->encode('Beograd'));
        $this->assertSame('Munze konza torima', $this->encoder->encode('Zemun zakon matori'));
        $this->assertSame('bijasr', $this->encoder->encode('srbija'));
        $this->assertSame('bari biri zegri pre', $this->encoder->encode('riba ribi grize rep'));
    }

    public function testBasicExamplesCyrillicAndMixed(): void
    {
        $this->assertSame('Мунзе конза торима', $this->encoder->encode('Земун закон матори'));
        $this->assertSame('Munze конза torima', $this->encoder->encode('Zemun закон matori'));
    }

    public function testCaseAndExceptions(): void
    {
        $encoder = new Satrovacki(exceptions: ['brate' => 'tebra']);
        $this->assertSame('tebra TEBRA Tebra', $encoder->encode('brate BRATE Brate'));
    }

    public function testPunctuationAndNumbers(): void
    {
        $this->assertSame('123, deaj!', $this->encoder->encode('123, ajde!'));
    }

    public function testMinWordLength(): void
    {
        $encoder = new Satrovacki(minWordLength: 5);
        $this->assertSame('riba rep', $encoder->encode('riba rep'));
    }

    public function testNoVowelFallback(): void
    {
        $this->assertSame('mnh', $this->encoder->encode('hmn'));
        $this->assertSame('стпр', $this->encoder->encode('прст'));
    }

    public function testDecodeExamples(): void
    {
        $this->assertSame('zemun zakon', $this->encoder->decode('munze konza'));
        $this->assertSame('Zemun zakon', $this->encoder->decode('Munze konza'));
        $this->assertSame('земун закон', $this->encoder->decode('мунзе конза'));
        $this->assertSame('srbija', $this->encoder->decode('bijasr'));
        $this->assertSame('matori', $this->encoder->decode('torima'));
        $this->assertSame('zemun zakon', $this->encoder->decode('zemun zakon'));
    }

    public function testCanDecodeWord(): void
    {
        $this->assertTrue($this->encoder->canDecodeWord('munze'));
        $this->assertTrue($this->encoder->canDecodeWord('конза'));
        $this->assertFalse($this->encoder->canDecodeWord('zemun'));
    }

    public function testDecodeShortWordAndCustomExceptionPath(): void
    {
        $this->assertSame('ab', $this->encoder->decodeWord('ab'));
        $custom = new Satrovacki(exceptions: ['brate' => 'tebra']);
        $this->assertSame('brate', $custom->decodeWord('tebra'));
    }

    public function testCanDecodeWordShortAndEncodeLatinHelpers(): void
    {
        $this->assertFalse($this->encoder->canDecodeWord('ab'));
        $custom = new Satrovacki(exceptions: ['brate' => 'tebra']);
        $this->assertTrue($custom->canDecodeWord('tebra'));
        $this->assertSame('tebra', $custom->_encodeLatinWord('brate'));
        $this->assertSame('a', $this->encoder->_encodeLatinWord('a'));
    }

    public function testRCanBeSyllabicVowel(): void
    {
        $this->assertSame('bijasr', $this->encoder->encode('srbija'));
        $this->assertFalse($this->encoder->_isVowelAt('abc', -1));
        $this->assertFalse($this->encoder->_isVowelAt('abc', 10));
    }

    public function testPlainCTargetVariants(): void
    {
        $this->assertSame('ц Ц цаса', Transliteration::latinToCyrillic('c C casa', plainCTarget: 'ц'));
        $this->assertSame('ч Ч часа', Transliteration::latinToCyrillic('c C casa', plainCTarget: 'ч'));
        $this->assertSame('ћ Ћ ћаса', Transliteration::latinToCyrillic('c C casa', plainCTarget: 'ћ'));
    }

    public function testTjDefaultAndSoftMode(): void
    {
        $this->assertSame('тј Тј ТЈ', Transliteration::latinToCyrillic('tj Tj TJ'));
        $this->assertSame('ћ Ћ Ћ', Transliteration::latinToCyrillic('tj Tj TJ', useTjForC: true));
    }

    public function testSoftTjOptionThroughEncoder(): void
    {
        $strict = new Satrovacki(exceptions: ['test' => 'tj']);
        $soft = new Satrovacki(exceptions: ['test' => 'tj'], softTjToCyrillic: true);

        $this->assertSame('тј', $strict->encode('тест'));
        $this->assertSame('ћ', $soft->encode('тест'));
    }

    public function testInvalidPlainCTargetRaises(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Satrovacki(plainCTarget: 'x');
    }
}
