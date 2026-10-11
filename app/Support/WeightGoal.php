<?php

namespace App\Support;

class WeightGoal
{
    public static function reached(float|int|string|null $weight, float|int|string|null $goal): bool
    {
        return $weight !== null && $goal !== null
            && round((float) $weight, 2) <= round((float) $goal, 2);
    }
}
