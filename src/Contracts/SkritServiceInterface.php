<?php

declare(strict_types=1);

namespace Skrit\Contracts;

interface SkritServiceInterface
{
    /**
     * @param array<string, mixed> $options
     * @return array{0:string,1:string}
     */
    public function encodeText(string $text, array $options = []): array;

    /**
     * @param array<string, mixed> $options
     * @return array{0:string,1:string}
     */
    public function decodeText(string $text, array $options = []): array;
}
