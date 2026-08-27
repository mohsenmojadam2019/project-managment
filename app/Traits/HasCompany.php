<?php

namespace App\Traits;

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Hekmatinasser\Verta\Verta;
use Carbon\Carbon;

trait HasCompany
{

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope());
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Format date based on the app locale.
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function translatedFormat($format)
    {
       if (app()->getLocale() == 'fa') {
            return (new Verta($this))->format($format);
        }

        return Carbon::parse($this)->translatedFormat($format);
    }

}
