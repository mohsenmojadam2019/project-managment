<?php

use Hekmatinasser\Verta\Verta;

if (! function_exists('jalali_date')) {
    /**
     * Format a Gregorian date/time for presentation in the Jalali calendar.
     *
     * Database and API values stay Gregorian. This helper is presentation-only
     * and is safe to use from HTTP, CLI, queues and tests.
     *
     * @param mixed $value
     * @return mixed
     */
    function jalali_date($value, ?string $format = null)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $resolvedFormat = $format ?: 'Y/m/d';

            if ($format === null && function_exists('Company')) {
                try {
                    $company = Company();

                    if ($company && ! empty($company->date_format)) {
                        $resolvedFormat = $company->date_format;
                    }
                } catch (\Throwable $exception) {
                    // Company context is optional in CLI, queue and test runs.
                }
            }

            return (new Verta($value))->format($resolvedFormat);
        } catch (\Throwable $exception) {
            // A display helper must never break the page because of bad legacy data.
            return $value;
        }
    }
}

if (! function_exists('ctj')) {
    /**
     * Backward-compatible alias used throughout the existing application.
     * All displayed dates are Jalali regardless of the authenticated locale.
     *
     * @param mixed $value
     * @return mixed
     */
    function ctj($value, ?string $format = null)
    {
        return jalali_date($value, $format);
    }
}

if (! function_exists('ytj')) {
    /**
     * Return the Jalali year corresponding to a Gregorian year.
     * Mid-year is used so the result is deterministic and not dependent on today.
     */
    function ytj($year): int
    {
        $date = Verta::GregorianToJalali((int) $year, 7, 1);

        return (int) $date[0];
    }
}

if (! function_exists('mtj')) {
    /**
     * Return the Jalali month containing the first day of a Gregorian month.
     */
    function mtj($month, $year = null): ?int
    {
        $month = (int) $month;

        if ($month < 1 || $month > 12) {
            return null;
        }

        $date = Verta::GregorianToJalali((int) ($year ?: date('Y')), $month, 1);

        return (int) $date[1];
    }
}

if (! function_exists('jdaysInMonth')) {
    /**
     * Return the number of days in a Jalali month.
     */
    function jdaysInMonth($month, $year = null): int
    {
        $month = (int) $month;

        if ($month < 1 || $month > 12) {
            return 0;
        }

        if ($year === null) {
            $today = Verta::GregorianToJalali((int) date('Y'), (int) date('m'), (int) date('d'));
            $year = $today[0];
        }

        return (int) Verta::getJalaliMonthDays((int) $year, $month);
    }
}
