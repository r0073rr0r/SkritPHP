<?php

declare(strict_types=1);

namespace Skrit\Laravel;

use Skrit\Contracts\SkritServiceInterface;
use Skrit\Leet;
use Skrit\Skrit;

class SkritService implements SkritServiceInterface
{
    /**
     * @param array<string, mixed> $defaults
     */
    public function __construct(private array $defaults = [])
    {
    }

    /**
     * @param array<string, mixed> $options
     * @return array{0:string,1:string}
     */
    public function encodeText(string $text, array $options = []): array
    {
        $params = $this->mergeOptions($options);

        return Skrit::encodeText(
            text: $text,
            mode: (string) $params['mode'],
            detectFrom: isset($params['detect_from']) ? (string) $params['detect_from'] : null,
            minWordLength: (int) $params['min_word_length'],
            plainCTarget: (string) $params['plain_c_target'],
            softTjToCyrillic: (bool) $params['soft_tj_to_cyrillic'],
            leetBase: (string) $params['leet_base'],
            leetProfile: (string) $params['leet_profile'],
            leetComplexity: (int) $params['leet_complexity'],
            leetDensity: (float) $params['leet_density'],
            zaStyle: (string) $params['za_style'],
            njeStyle: (string) $params['nje_style'],
            utroPrefix: (string) $params['utro_prefix'],
            utroInfix: (string) $params['utro_infix'],
            utroSuffix: (string) $params['utro_suffix'],
        );
    }

    /**
     * @param array<string, mixed> $options
     * @return array{0:string,1:string}
     */
    public function decodeText(string $text, array $options = []): array
    {
        $options['mode'] = 'auto';
        return $this->encodeText($text, $options);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function mergeOptions(array $options): array
    {
        $defaults = [
            'mode' => 'auto',
            'detect_from' => null,
            'min_word_length' => 3,
            'plain_c_target' => 'ц',
            'soft_tj_to_cyrillic' => false,
            'leet_base' => 'auto',
            'leet_profile' => 'basic',
            'leet_complexity' => 0,
            'leet_density' => Leet::DEFAULT_LEET_DENSITY,
            'za_style' => '24',
            'nje_style' => 'n73',
            'utro_prefix' => 'u',
            'utro_infix' => 'za',
            'utro_suffix' => 'nje',
        ];

        return array_replace($defaults, $this->defaults, $options);
    }
}
