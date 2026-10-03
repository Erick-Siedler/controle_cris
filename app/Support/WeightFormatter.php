<?php

namespace App\Support;

class WeightFormatter
{
    public static function display(float|int|string $weight): string
    {
        $rounded = round((float) $weight, 1, PHP_ROUND_HALF_UP);

        if ($rounded == 0.0) {
            $rounded = 0.0;
        }

        return number_format($rounded, $rounded == round($rounded) ? 0 : 1, ',', '.');
    }
}
