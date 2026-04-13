<?php

declare(strict_types=1);

namespace Skrit\Tests\Feature;

use Skrit\Contracts\SkritServiceInterface;
use Skrit\Laravel\Facades\Skrit as SkritFacade;

final class SkritLaravelIntegrationTest extends LaravelTestCase
{
    public function testServiceIsBoundAndUsable(): void
    {
        $service = $this->app->make(SkritServiceInterface::class);
        [$encoded, $mode] = $service->encodeText('Zemun zakon matori', ['mode' => 'auto']);

        $this->assertSame('satro', $mode);
        $this->assertSame('Munze konza torima', $encoded);
    }

    public function testFacadeUsage(): void
    {
        [$encoded, $mode] = SkritFacade::encodeText('bazen', ['mode' => 'utro']);
        $this->assertSame('utro', $mode);
        $this->assertSame('uzenzabanje', $encoded);
    }

    public function testDecodeMethod(): void
    {
        $service = $this->app->make(SkritServiceInterface::class);
        [$decoded, $mode] = $service->decodeText('munze konza');

        $this->assertSame('satro', $mode);
        $this->assertSame('zemun zakon', $decoded);
    }
}
