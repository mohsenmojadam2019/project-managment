<?php

namespace App\Http\Middleware;

use App\Models\Contract;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Scopes\ActiveScope;
use Closure;
use Illuminate\Http\Request;

class ValidatePublicDocumentAccess
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $routeName = $route?->getName();

        if ($routeName === 'front.contract.show') {
            $contract = Contract::where('hash', $route->parameter('hash'))
                ->withoutGlobalScope(ActiveScope::class)
                ->firstOrFail();
            $request->session()->put($this->sessionKey('contract', $contract->id), true);
            return $next($request);
        }

        if ($routeName === 'front.estimate.show') {
            $estimate = Estimate::where('hash', $route->parameter('hash'))->firstOrFail();
            $request->session()->put($this->sessionKey('estimate', $estimate->id), true);
            return $next($request);
        }

        if ($routeName === 'front.proposal') {
            $proposal = Proposal::where('hash', $route->parameter('hash'))->firstOrFail();
            $request->session()->put($this->sessionKey('proposal', $proposal->id), true);
            return $next($request);
        }

        if ($routeName === 'front.invoice') {
            $invoice = Invoice::where('hash', $route->parameter('hash'))->firstOrFail();
            $request->session()->put($this->sessionKey('invoice', $invoice->id), true);
            return $next($request);
        }

        if ($routeName === 'front.contract.sign') {
            $this->requireSessionAccess($request, 'contract', $route->parameter('id'));
        }

        if (in_array($routeName, ['front.estimate.accept', 'front.estimate.decline'], true)) {
            $this->requireSessionAccess($request, 'estimate', $route->parameter('id'));
        }

        if ($routeName === 'front.proposal_action') {
            $this->requireSessionAccess($request, 'proposal', $route->parameter('id'));
        }

        if ($routeName === 'front.contract.download') {
            $contract = Contract::where('hash', $route->parameter('id'))
                ->withoutGlobalScope(ActiveScope::class)
                ->firstOrFail();
            $this->requireSessionAccess($request, 'contract', $contract->id);
        }

        if ($routeName === 'front.estimate.download') {
            $estimate = Estimate::where('hash', $route->parameter('id'))->firstOrFail();
            $this->requireSessionAccess($request, 'estimate', $estimate->id);
        }

        if ($routeName === 'front.download_proposal') {
            $proposal = Proposal::where('hash', $route->parameter('id'))->firstOrFail();
            $this->requireSessionAccess($request, 'proposal', $proposal->id);
        }

        if ($routeName === 'front.invoice_download') {
            $invoice = Invoice::whereRaw('md5(id) = ?', [$route->parameter('id')])->firstOrFail();
            $this->requireSessionAccess($request, 'invoice', $invoice->id);
        }

        if ($routeName === 'front.invoice_payment_failed') {
            $this->requireSessionAccess($request, 'invoice', $route->parameter('invoiceId'));
        }

        if ($routeName === 'front.save_stripe_detail') {
            $this->requireSessionAccess($request, 'invoice', $request->input('invoice_id'));
        }

        if ($routeName === 'front.stripe_modal') {
            $this->requireSessionAccess($request, 'invoice', $request->input('invoice_id'));
        }

        if (in_array($routeName, ['front.paystack_modal', 'front.flutterwave_modal', 'front.mollie_modal', 'front.authorize_modal'], true)
            && $request->input('type', 'invoice') === 'invoice') {
            $this->requireSessionAccess($request, 'invoice', $request->input('id'));
        }

        return $next($request);
    }

    private function requireSessionAccess(Request $request, string $type, $id): void
    {
        abort_unless(
            $id && $request->session()->get($this->sessionKey($type, $id), false),
            403
        );
    }

    private function sessionKey(string $type, $id): string
    {
        return 'public_document_access.' . $type . '.' . $id;
    }
}
