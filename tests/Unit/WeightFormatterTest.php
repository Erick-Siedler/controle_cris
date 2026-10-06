<?php

namespace Tests\Unit;

use App\Support\WeightFormatter;
use PHPUnit\Framework\TestCase;

class WeightFormatterTest extends TestCase
{
    public function test_displays_only_significant_decimals_with_a_comma(): void
    {
        $this->assertSame('57', WeightFormatter::display(57.00));
        $this->assertSame('57,5', WeightFormatter::display(57.50));
        $this->assertSame('57,55', WeightFormatter::display(57.55));
        $this->assertSame('72', WeightFormatter::display(72.00));
        $this->assertSame('73,2', WeightFormatter::display(73.20));
        $this->assertSame('71,95', WeightFormatter::display(71.95));
        $this->assertSame('-0,01', WeightFormatter::display(-0.01));
        $this->assertSame('0', WeightFormatter::display(-0.001));
    }
}
