<?php

namespace App\Http\Middleware;

use App\Models\Contract;
use App\Models\Estimate;
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

        if ($routeName === 'front.contract.sign') {
            abort_unless(
                $request->session()->get($this->sessionKey('contract', $route->parameter('id')), false),
                403
            );
        }

        if (in_array($routeName, ['front.estimate.accept', 'front.estimate.decline'], true)) {
            abort_unless(
                $request->session()->get($this->sessionKey('estimate', $route->parameter('id')), false),
                403
            );
        }

        return $next($request);
    }

    private function sessionKey(string $type, $id): string
    {
        return 'public_document_access.' . $type . '.' . $id;
    }
}
