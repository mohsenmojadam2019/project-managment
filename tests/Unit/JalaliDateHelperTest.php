<?php

namespace Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class JalaliDateHelperTest extends TestCase
{
    public function test_it_converts_a_known_gregorian_date_to_jalali(): void
    {
        $this->assertSame('1403/01/01', ctj('2024-03-20', 'Y/m/d'));
    }

    public function test_it_accepts_datetime_interface_values(): void
    {
        $date = new DateTimeImmutable('2024-03-20 12:30:00');

        $this->assertSame('1403/01/01', jalali_date($date, 'Y/m/d'));
    }

    public function test_it_keeps_empty_values_unchanged(): void
    {
        $this->assertNull(ctj(null));
        $this->assertSame('', ctj(''));
    }

    public function test_it_does_not_throw_for_invalid_legacy_date_values(): void
    {
        $this->assertSame('not-a-date', ctj('not-a-date', 'Y/m/d'));
    }

    public function test_it_returns_jalali_month_lengths(): void
    {
        $this->assertSame(31, jdaysInMonth(1, 1403));
        $this->assertSame(30, jdaysInMonth(7, 1403));
        $this->assertSame(0, jdaysInMonth(13, 1403));
    }

    public function test_year_and_month_conversion_is_deterministic(): void
    {
        $this->assertSame(1403, ytj(2024));
        $this->assertSame(12, mtj(3, 2024));
    }
}
