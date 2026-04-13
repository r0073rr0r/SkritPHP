<?php

declare(strict_types=1);

namespace Skrit\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Skrit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'skrit';
    }
}
