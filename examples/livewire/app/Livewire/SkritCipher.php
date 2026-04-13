<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Skrit\Contracts\SkritServiceInterface;

class SkritCipher extends Component
{
    public string $input = 'Zemun zakon matori';
    public string $mode = 'auto';
    public ?string $detectFrom = null;

    public string $plainCTarget = 'ц';
    public bool $softTjToCyrillic = false;

    public string $leetBase = 'auto';
    public string $leetProfile = 'basic';
    public int $leetComplexity = 0;
    public float $leetDensity = 0.86;
    public string $zaStyle = '24';
    public string $njeStyle = 'n73';

    public string $utroPrefix = 'u';
    public string $utroInfix = 'za';
    public string $utroSuffix = 'nje';

    public string $output = '';
    public string $resolvedMode = 'satro';

    public function mount(): void
    {
        $this->transform();
    }

    public function updated($property): void
    {
        if ($property === 'output' || $property === 'resolvedMode') {
            return;
        }

        $this->transform();
    }

    public function transform(): void
    {
        /** @var SkritServiceInterface $service */
        $service = app(SkritServiceInterface::class);

        [$text, $mode] = $service->encodeText($this->input, [
            'mode' => $this->mode,
            'detect_from' => $this->detectFrom,
            'plain_c_target' => $this->plainCTarget,
            'soft_tj_to_cyrillic' => $this->softTjToCyrillic,
            'leet_base' => $this->leetBase,
            'leet_profile' => $this->leetProfile,
            'leet_complexity' => $this->leetComplexity,
            'leet_density' => $this->leetDensity,
            'za_style' => $this->zaStyle,
            'nje_style' => $this->njeStyle,
            'utro_prefix' => $this->utroPrefix,
            'utro_infix' => $this->utroInfix,
            'utro_suffix' => $this->utroSuffix,
        ]);

        $this->output = $text;
        $this->resolvedMode = $mode;
    }

    public function render()
    {
        return view('livewire.skrit-cipher');
    }
}
