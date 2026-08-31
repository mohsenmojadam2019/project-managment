<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'paystack-webhook/*',
        'flutterwave-webhook/*',
        'mollie-webhook/*',
        'payfast-webhook/*',
        'square-webhook/*',
        'razorpay-webhook/*',
        'paypal-webhook/*',
        'verify-webhook/*',
        '/lead-form/leadStore',
        '/lead-form/ticket-store',
    ];
}
