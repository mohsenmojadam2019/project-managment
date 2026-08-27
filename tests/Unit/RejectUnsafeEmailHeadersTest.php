<?php

namespace Tests\Unit;

use App\Http\Middleware\RejectUnsafeEmailHeaders;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RejectUnsafeEmailHeadersTest extends TestCase
{
    public function test_it_allows_normal_email_input(): void
    {
        $request = Request::create('/test', 'POST', ['email' => 'user@example.com']);
        $middleware = new RejectUnsafeEmailHeaders();

        $response = $middleware->handle($request, fn () => new Response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_it_rejects_crlf_in_email_field(): void
    {
        $request = Request::create('/test', 'POST', [
            'email' => "user@example.com\r\nBcc: attacker@example.com",
        ]);
        $middleware = new RejectUnsafeEmailHeaders();

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(0);

        try {
            $middleware->handle($request, fn () => new Response('ok', 200));
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
            throw $exception;
        }
    }

    public function test_it_rejects_nested_email_input(): void
    {
        $request = Request::create('/test', 'POST', [
            'contact' => ['email_address' => "user@example.com\nCc: attacker@example.com"],
        ]);
        $middleware = new RejectUnsafeEmailHeaders();

        try {
            $middleware->handle($request, fn () => new Response('ok', 200));
            $this->fail('Unsafe nested email input was not rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    public function test_it_allows_multiline_non_email_text(): void
    {
        $request = Request::create('/test', 'POST', ['message' => "line one\nline two"]);
        $middleware = new RejectUnsafeEmailHeaders();

        $response = $middleware->handle($request, fn () => new Response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }
}
