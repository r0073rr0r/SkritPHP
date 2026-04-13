<?php

declare(strict_types=1);

namespace Skrit\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Skrit\Leet;
use Skrit\LeetEncoder;

final class LeetTest extends TestCase
{
    public function testProfilesAndTable(): void
    {
        $this->assertContains('basic', Leet::availableProfiles());
        $this->assertContains('readable', Leet::availableProfiles());
        $this->assertContains('full', Leet::availableProfiles());
        $this->assertCount(26, Leet::LEET_TABLE);
        $this->assertSame(0.86, Leet::DEFAULT_LEET_DENSITY);
    }

    public function testGetProfileValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Leet::getLeetProfile('missing');
    }

    public function testBasicAndFullEncoding(): void
    {
        $basic = Leet::getLeetProfile('basic');
        $full = Leet::getLeetProfile('full');

        $this->assertSame('23m00n 24k0n m470r1', Leet::applyLeet('Zemun zakon matori', $basic, density: 1.0));
        $this->assertSame('4I3[><j2', Leet::applyLeet('abcxyz', $full));
    }

    public function testLooksLikeLeet(): void
    {
        $this->assertTrue(Leet::looksLikeLeet('M00n23 k0n24 70r1m4'));
        $this->assertFalse(Leet::looksLikeLeet('Zemun zakon matori'));
    }

    public function testEncoderClass(): void
    {
        $this->assertSame('m470r1', (new LeetEncoder(profile: 'basic'))->encode('matori'));
    }

    public function testDensityControl(): void
    {
        $mapping = Leet::getLeetProfile('basic');
        $this->assertSame('matori', Leet::applyLeet('matori', $mapping, density: 0.0));
        $this->assertSame('m470r1', Leet::applyLeet('matori', $mapping, density: 1.0));
    }

    public function testCustomProfileAndUppercaseAlphaReplacement(): void
    {
        $mapping = Leet::getLeetProfile(customMap: ['a' => 'x', 'b' => 'yz']);
        $this->assertSame('XYZ', Leet::applyLeet('AB', $mapping));
    }

    public function testInvalidDensityRaises(): void
    {
        $mapping = Leet::getLeetProfile('basic');
        $this->expectException(InvalidArgumentException::class);
        Leet::applyLeet('abc', $mapping, density: 1.1);
    }

    public function testFullProfileComplexity(): void
    {
        $c0 = Leet::buildFullLeetProfile(0);
        $c1 = Leet::buildFullLeetProfile(1);

        $this->assertSame('4', $c0['a']);
        $this->assertSame('/\\', $c1['a']);
        $this->assertSame('ri2', $c0['r']);
        $this->assertSame('9', $c1['r']);
    }

    public function testInvalidComplexityRaises(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Leet::buildFullLeetProfile(-1);
    }

    public function testEncoderComplexity(): void
    {
        $this->assertSame('/\\8', (new LeetEncoder(profile: 'full', complexity: 1))->encode('ab'));
    }
}
