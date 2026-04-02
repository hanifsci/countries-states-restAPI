<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('get') || $request->user() !== null || $request->bearerToken() !== null) {
            return $next($request);
        }

        $key = 'response:' . sha1($request->fullUrl());
        $ttl = now()->addSeconds(max((int) config('cache.api_client_cache_ttl', 60), 1));

        if ($cached = Cache::get($key)) {
            return response($cached['content'], $cached['status'], $cached['headers']);
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->allPreserveCaseWithoutCookies(),
            ], $ttl);
        }

        return $response;
    }
}