<?php

declare(strict_types=1);

use Skrit\Leet;

return [
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
