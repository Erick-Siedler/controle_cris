<?php

namespace Tests\Unit;

use App\Support\WeightFormatter;
use PHPUnit\Framework\TestCase;

class WeightFormatterTest extends TestCase
{
    public function test_displays_at_most_one_decimal_with_a_comma(): void
    {
        $this->assertSame('72', WeightFormatter::display(72.00));
        $this->assertSame('73,2', WeightFormatter::display(73.20));
        $this->assertSame('72', WeightFormatter::display(71.95));
        $this->assertSame('0', WeightFormatter::display(-0.01));
    }
}
