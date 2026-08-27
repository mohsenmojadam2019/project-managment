<?php

namespace App\Http\Controllers\Payment;

use App\Helper\Reply;
use App\Helper\StripeAmount;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentGatewayCredentials;
use App\Traits\MakePaymentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeController extends Controller
{
    use MakePaymentTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.stripe');
    }

    public function paymentWithStripe(Request $request, $id)
    {
        $redirectRoute = 'invoices.show';
        $invoice = Invoice::with(['currency', 'company'])->findOrFail($id);
        $param = 'invoice';

        if ($request->type === 'order') {
            $redirectRoute = 'orders.show';
            $param = 'order';
            $invoice = Invoice::with(['currency', 'company'])->where('order_id', $id)->latest()->firstOrFail();
        }

        $verification = $this->verifyPaymentIntent($invoice, $request->paymentIntentId);

        if ($verification !== true) {
            return Reply::error($verification);
        }

        $amount = $invoice->amountDue();

        if ($amount <= 0) {
            return Reply::error(__('messages.invoiceAlreadyPaid'));
        }

        $this->makePayment('Stripe', $amount, $invoice, $request->paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();

        return $this->makeStripePayment($redirectRoute, $id, $param);
    }

    public function paymentWithStripePublic(Request $request, $hash)
    {
        $invoice = Invoice::with(['currency', 'company'])->where('hash', $hash)->firstOrFail();
        $verification = $this->verifyPaymentIntent($invoice, $request->paymentIntentId);

        if ($verification !== true) {
            return Reply::error($verification);
        }

        $amount = $invoice->amountDue();

        if ($amount <= 0) {
            return Reply::error(__('messages.invoiceAlreadyPaid'));
        }

        $this->makePayment('Stripe', $amount, $invoice, $request->paymentIntentId, 'complete');
        $invoice->status = 'paid';
        $invoice->save();

        return $this->makeStripePayment('front.invoice', $hash, 'hash');
    }

    private function verifyPaymentIntent(Invoice $invoice, ?string $paymentIntentId): bool|string
    {
        if (!$paymentIntentId) {
            return 'Stripe payment reference is missing.';
        }

        if (!$invoice->currency || !$invoice->currency->currency_code) {
            return 'Invoice currency is not configured.';
        }

        $credentials = PaymentGatewayCredentials::where('company_id', $invoice->company_id)->first();

        if (!$credentials || $credentials->stripe_status !== 'active') {
            return 'Stripe payment is not enabled for this company.';
        }

        $stripeSecret = $credentials->stripe_mode === 'test'
            ? $credentials->test_stripe_secret
            : $credentials->live_stripe_secret;

        if (!$stripeSecret) {
            return 'Stripe payment credentials are incomplete.';
        }

        try {
            Stripe::setApiKey($stripeSecret);
            $intent = PaymentIntent::retrieve($paymentIntentId);
        }
        catch (\Throwable $e) {
            report($e);
            return 'Unable to verify the Stripe payment.';
        }

        $currency = strtoupper($invoice->currency->currency_code);
        $expectedAmount = StripeAmount::toMinorUnits($invoice->amountDue(), $currency);
        $metadataInvoiceId = $intent->metadata['invoice_id'] ?? null;

        if ($intent->status !== 'succeeded') {
            return 'Stripe payment has not succeeded.';
        }

        if (strtoupper((string) $intent->currency) !== $currency) {
            return 'Stripe payment currency does not match the invoice.';
        }

        if ((int) $intent->amount !== $expectedAmount || (int) $intent->amount_received < $expectedAmount) {
            return 'Stripe payment amount does not match the invoice.';
        }

        if ((int) $metadataInvoiceId !== (int) $invoice->id) {
            return 'Stripe payment does not belong to this invoice.';
        }

        return true;
    }

    private function makeStripePayment($redirectRoute, $id, $param = null)
    {
        $param = $param ?? 'invoice';
        $signedUrl = url()->temporarySignedRoute(
            $redirectRoute,
            now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY),
            [$param => $id]
        );

        Session::put('success', __('messages.paymentSuccessful'));

        return Reply::redirect($signedUrl, __('messages.paymentSuccessful'));
    }
}
