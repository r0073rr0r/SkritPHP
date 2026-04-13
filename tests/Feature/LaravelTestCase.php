<?php

declare(strict_types=1);

namespace Skrit\Tests\Feature;

use Orchestra\Testbench\TestCase as Orchestra;
use Skrit\Laravel\SkritServiceProvider;

abstract class LaravelTestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [SkritServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('skrit.mode', 'auto');
        $app['config']->set('skrit.min_word_length', 3);
    }
}
