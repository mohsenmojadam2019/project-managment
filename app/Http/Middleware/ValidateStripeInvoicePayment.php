<?php

namespace App\Http\Middleware;

use App\Helper\Reply;
use App\Models\Invoice;
use App\Models\PaymentGatewayCredentials;
use Closure;
use Illuminate\Http\Request;

class ValidateStripeInvoicePayment
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->routeIs('front.save_stripe_detail')) {
            return $next($request);
        }

        $invoiceId = $request->input('invoice_id');

        if (!$invoiceId) {
            return response()->json(Reply::error(__('validation.required', ['attribute' => 'invoice_id'])), 422);
        }

        $invoice = Invoice::with(['client', 'project.client', 'currency', 'company'])->find($invoiceId);

        if (!$invoice) {
            abort(404);
        }

        if ($invoice->amountDue() <= 0) {
            return response()->json(Reply::error(__('messages.invoiceAlreadyPaid')), 422);
        }

        if (!$invoice->company) {
            return response()->json(Reply::error('Invoice company is not available.'), 422);
        }

        $credentials = PaymentGatewayCredentials::where('company_id', $invoice->company->id)->first();
        $stripeSecret = $credentials
            ? ($credentials->stripe_mode === 'test' ? $credentials->test_stripe_secret : $credentials->live_stripe_secret)
            : null;

        if (!$credentials || !$stripeSecret) {
            return response()->json(Reply::error('Stripe payment is not configured for this company.'), 422);
        }

        $client = $invoice->client;

        if (!$client && $invoice->project) {
            $client = $invoice->project->client;
        }

        if (!$client || !$client->email) {
            return response()->json(Reply::error('A valid invoice client email is required for Stripe payment.'), 422);
        }

        if (!$invoice->currency || !$invoice->currency->currency_code) {
            return response()->json(Reply::error('A valid invoice currency is required for Stripe payment.'), 422);
        }

        return $next($request);
    }
}
