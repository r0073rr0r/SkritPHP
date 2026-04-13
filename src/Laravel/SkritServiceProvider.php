<?php

declare(strict_types=1);

namespace Skrit\Laravel;

use Illuminate\Support\ServiceProvider;
use Skrit\Contracts\SkritServiceInterface;

class SkritServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/skrit.php', 'skrit');

        $this->app->singleton(SkritServiceInterface::class, function ($app): SkritService {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('skrit', []);
            return new SkritService($config);
        });

        $this->app->alias(SkritServiceInterface::class, 'skrit');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/skrit.php' => config_path('skrit.php'),
        ], 'skrit-config');
    }
}
