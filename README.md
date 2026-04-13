# SkritPHP

[![CI](https://img.shields.io/github/actions/workflow/status/r0073rr0r/SkritPHP/ci.yml?branch=main&label=CI)](https://github.com/r0073rr0r/SkritPHP/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/r0073rr0r/skritphp)](https://packagist.org/packages/r0073rr0r/skritphp)
[![PHP](https://img.shields.io/packagist/php-v/r0073rr0r/skritphp)](https://packagist.org/packages/r0073rr0r/skritphp)
[![License: GPL-3.0-or-later](https://img.shields.io/badge/License-GPL--3.0--or--later-blue.svg)](LICENSE)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)](#testing)
[![Packagist Downloads](https://img.shields.io/packagist/dm/r0073rr0r/skritphp)](https://packagist.org/packages/r0073rr0r/skritphp)
[![GitHub Stars](https://img.shields.io/github/stars/r0073rr0r/SkritPHP?style=social)](https://github.com/r0073rr0r/SkritPHP/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/r0073rr0r/SkritPHP?style=social)](https://github.com/r0073rr0r/SkritPHP/network/members)
[![GitHub Issues](https://img.shields.io/github/issues/r0073rr0r/SkritPHP)](https://github.com/r0073rr0r/SkritPHP/issues)
[![Last Commit](https://img.shields.io/github/last-commit/r0073rr0r/SkritPHP)](https://github.com/r0073rr0r/SkritPHP/commits/main)

PHP port of Python `skrit` (`v0.5.x`) for Serbian slang-style text transforms:

- `satrovacki`
- `utrovacki`
- `leetrovacki`
- unified `auto` router with encode/decode detection

Repo/package identity:
- GitHub: `r0073rr0r/SkritPHP`
- Composer: `r0073rr0r/skritphp`

## Requirements

- PHP `^8.2`
- ext-mbstring

## Install

```bash
composer require r0073rr0r/skritphp
```

## Pure PHP Usage

### Unified Router (`Skrit::encodeText`)

```php
<?php

use Skrit\Skrit;

[$encoded, $mode] = Skrit::encodeText('Zemun zakon matori', mode: 'auto');

// $mode = 'satro'
// $encoded = 'Munze konza torima'
```

### Force specific mode

```php
<?php

use Skrit\Skrit;

[$satro] = Skrit::encodeText('bazen', mode: 'satro'); // zenba
[$utro] = Skrit::encodeText('bazen', mode: 'utro');   // uzenzabanje
[$leet] = Skrit::encodeText('bazen', mode: 'leet', leetBase: 'satro'); // 23nb4
```

### Direct transformer classes

```php
<?php

use Skrit\Satrovacki;
use Skrit\Utrovacki;
use Skrit\Leetrovacki;

$satro = new Satrovacki();
echo $satro->encode('Beograd'); // Gradbeo

$utro = new Utrovacki(prefix: 'x', infix: 'yy', suffix: 'zz');
echo $utro->encode('bazen'); // xzenyybazz

$leet = new Leetrovacki(baseMode: 'utro', leetDensity: 1.0);
echo $leet->encode('bazen'); // 00zen24ban73
```

## Supported Router Options

- `mode`: `auto|satro|utro|leet`
- `detectFrom`
- `minWordLength`
- `plainCTarget`: `ц|ч|ћ`
- `softTjToCyrillic`
- `leetBase`: `auto|satro|utro`
- `leetProfile`: `basic|readable|full`
- `leetComplexity`
- `leetDensity`
- `zaStyle`: `24|z4`
- `njeStyle`: `n73|nj3|њ`
- `utroPrefix`, `utroInfix`, `utroSuffix`

## Laravel Usage

Auto-discovery is enabled in `composer.json`.

### Publish config

```bash
php artisan vendor:publish --tag=skrit-config
```

### Facade

```php
<?php

use Skrit\Laravel\Facades\Skrit;

[$encoded, $mode] = Skrit::encodeText('Zemun zakon matori', ['mode' => 'auto']);
```

### Dependency Injection

```php
<?php

use Skrit\Contracts\SkritServiceInterface;

final class CipherController
{
    public function __construct(private SkritServiceInterface $skrit) {}

    public function __invoke(): array
    {
        return $this->skrit->encodeText('uzenzabanje', ['mode' => 'auto']);
    }
}
```

## Livewire Example

Livewire sample component is included at:

- `examples/livewire/app/Livewire/SkritCipher.php`
- `examples/livewire/resources/views/livewire/skrit-cipher.blade.php`

To use it in your Laravel app:

1. Copy component class to `app/Livewire/SkritCipher.php`.
2. Copy blade view to `resources/views/livewire/skrit-cipher.blade.php`.
3. Render it in a blade page: `<livewire:skrit-cipher />`.

## Testing

Run full test suite:

```bash
composer test
```

Run strict gate (tests + enforced 100% coverage):

```bash
composer test:strict
```

Included tests cover:

- parity of core behavior against Python reference cases
- satro/utro/leet transformer rules
- unified auto router behavior
- Laravel service container + facade integration

## License

GPL-3.0-or-later

## Community

- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Contributing](CONTRIBUTING.md)
- [Security Policy](SECURITY.md)
- [Support](SUPPORT.md)
