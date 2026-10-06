<?php

namespace App\Support;

class WeightFormatter
{
    public static function display(float|int|string $weight): string
    {
        $rounded = round((float) $weight, 2, PHP_ROUND_HALF_UP);

        if ($rounded == 0.0) {
            $rounded = 0.0;
        }

        return rtrim(rtrim(number_format($rounded, 2, ',', '.'), '0'), ',');
    }
}
