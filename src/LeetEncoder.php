<?php

declare(strict_types=1);

namespace Skrit;

class LeetEncoder
{
    /** @var array<string, string> */
    private array $mapping;

    /**
     * @param array<string, string>|null $customMap
     */
    public function __construct(
        private string $profile = 'basic',
        private ?array $customMap = null,
        private int $complexity = 0,
        private float $density = Leet::DEFAULT_LEET_DENSITY,
    ) {
        $this->mapping = Leet::getLeetProfile($this->profile, $this->customMap, $this->complexity);
    }

    public function encode(string $text): string
    {
        return Leet::applyLeet($text, $this->mapping, $this->density);
    }
}
