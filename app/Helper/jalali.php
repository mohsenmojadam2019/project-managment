<?php

use Carbon\Carbon;
use DateTimeInterface;
use Hekmatinasser\Verta\Verta;

if (!function_exists('jalali_date_format')) {
    /**
     * Resolve the preferred display format without coupling date conversion
     * to an authenticated company context.
     */
    function jalali_date_format(?string $format = null): string
    {
        if ($format) {
            return $format;
        }

        try {
            if (function_exists('company')) {
                $company = company();

                if ($company && !empty($company->date_format)) {
                    return (string) $company->date_format;
                }
            }
        } catch (Throwable $exception) {
            // Company context is not always available (CLI, queue, install, tests).
        }

        return 'Y-m-d';
    }
}

if (!function_exists('jalali_carbon')) {
    /**
     * Normalize supported Gregorian date inputs to Carbon.
     */
    function jalali_carbon(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof DateTimeInterface) {
                return Carbon::instance($value);
            }

            if (is_int($value)) {
                return Carbon::createFromTimestamp($value);
            }

            if (is_string($value) && trim($value) !== '') {
                return Carbon::parse($value);
            }
        } catch (Throwable $exception) {
            return null;
        }

        return null;
    }
}

if (!function_exists('ctj')) {
    /**
     * Convert a Gregorian value to a Jalali display date for Persian UI.
     *
     * Database/API values remain Gregorian; conversion belongs to presentation.
     * Invalid/non-date values are returned untouched for backwards compatibility.
     */
    function ctj(mixed $value, ?string $format = null): mixed
    {
        if (app()->getLocale() !== 'fa') {
            return $value;
        }

        $date = jalali_carbon($value);

        if (!$date) {
            return $value;
        }

        try {
            return Verta::instance($date)->format(jalali_date_format($format));
        } catch (Throwable $exception) {
            return $value;
        }
    }
}

if (!function_exists('ctj_datetime')) {
    /**
     * Jalali date/time formatter for timestamps shown to users.
     */
    function ctj_datetime(mixed $value, ?string $format = null): mixed
    {
        return ctj($value, $format ?: jalali_date_format() . ' H:i');
    }
}

if (!function_exists('jalali_date')) {
    /**
     * Explicit alias for new code. Keep ctj() for legacy templates.
     */
    function jalali_date(mixed $value, ?string $format = null): mixed
    {
        return ctj($value, $format);
    }
}
