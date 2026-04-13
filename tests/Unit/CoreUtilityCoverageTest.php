<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Skrit\Core\CaseHelper;
use Skrit\Core\StringUtils;
use Skrit\Core\Tokenizer;
use Skrit\Core\Transliteration;

final class CoreUtilityCoverageTest extends TestCase
{
    public function testCaseHelperFallbackAndStaticPredicates(): void
    {
        $this->assertSame('MiXeD', CaseHelper::applyCase('mIxEd', 'MiXeD'));

        $this->assertFalse(CaseHelper::isLower(''));
        $this->assertFalse(CaseHelper::isLower('123'));

        $this->assertFalse(CaseHelper::isTitle(''));
        $this->assertFalse(CaseHelper::isTitle('ABc'));
    }

    public function testStringUtilsEdgeCases(): void
    {
        $this->assertSame([], StringUtils::splitChars(''));
        $this->assertTrue(StringUtils::startsWith('abc', ''));
        $this->assertTrue(StringUtils::endsWith('abc', ''));
    }

    public function testTokenizerEmptyInput(): void
    {
        $this->assertSame([], Tokenizer::splitWordOrOther(''));
    }

    public function testTransliterationIsCyrillicChar(): void
    {
        $this->assertTrue(Transliteration::isCyrillicChar('ж'));
        $this->assertFalse(Transliteration::isCyrillicChar('z'));
    }
}
