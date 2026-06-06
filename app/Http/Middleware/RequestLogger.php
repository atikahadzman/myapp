<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        // BEFORE request
        Log::info('Incoming Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
        ]);

        $response = $next($request);

        // AFTER request
        $duration = microtime(true) - $start;

        Log::info('Outgoing Response', [
            'status' => $response->status(),
            'duration_ms' => round($duration * 1000, 2),
            'user_id' => $request->user()?->id,
        ]);

        return $next($request);
    }
}
