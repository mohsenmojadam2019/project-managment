<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;

class JalaliDateHelperTest extends TestCase
{
    public function test_it_converts_gregorian_dates_to_jalali_for_persian_locale(): void
    {
        app()->setLocale('fa');

        $this->assertSame('1403/01/01', ctj('2024-03-20', 'Y/m/d'));
    }

    public function test_it_accepts_datetime_objects(): void
    {
        app()->setLocale('fa');

        $date = Carbon::create(2024, 3, 20, 12, 30, 0);

        $this->assertSame('1403/01/01 12:30', ctj_datetime($date, 'Y/m/d H:i'));
    }

    public function test_it_preserves_invalid_values(): void
    {
        app()->setLocale('fa');

        $this->assertSame('not-a-date', ctj('not-a-date'));
        $this->assertNull(ctj(null));
    }

    public function test_it_preserves_existing_behaviour_outside_persian_locale(): void
    {
        app()->setLocale('en');

        $this->assertSame('2024-03-20', ctj('2024-03-20', 'Y/m/d'));
    }
}
