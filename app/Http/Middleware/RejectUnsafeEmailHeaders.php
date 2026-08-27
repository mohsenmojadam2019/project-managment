<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RejectUnsafeEmailHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->containsUnsafeEmailInput($request->all())) {
            throw new HttpException(422, 'Invalid email input.');
        }

        return $next($request);
    }

    private function containsUnsafeEmailInput(array $input, string $path = ''): bool
    {
        foreach ($input as $key => $value) {
            $currentPath = $path === '' ? (string) $key : $path . '.' . $key;

            if (is_array($value)) {
                if ($this->containsUnsafeEmailInput($value, $currentPath)) {
                    return true;
                }

                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            $emailLikeField = str_contains(strtolower($currentPath), 'email');
            $emailLikeValue = str_contains($value, '@');

            if (($emailLikeField || $emailLikeValue)
                && (str_contains($value, "\r") || str_contains($value, "\n"))) {
                return true;
            }
        }

        return false;
    }
}
