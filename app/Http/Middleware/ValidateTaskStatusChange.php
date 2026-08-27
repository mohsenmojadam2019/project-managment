<?php

namespace App\Http\Middleware;

use App\Models\TaskboardColumn;
use Closure;
use Illuminate\Http\Request;

class ValidateTaskStatusChange
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->route()?->getName() !== 'tasks.change_status') {
            return $next($request);
        }

        $status = $request->input('status');

        if (!$status || !TaskboardColumn::where('slug', $status)->exists()) {
            $message = __('validation.exists', ['attribute' => __('app.status')]);

            return response()->json([
                'message' => $message,
                'errors' => [
                    'status' => [$message],
                ],
            ], 422);
        }

        return $next($request);
    }
}
