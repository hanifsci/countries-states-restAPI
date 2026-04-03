<?php

namespace App\Support;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ApiResponseCache
{
    public static function remember(string $namespace, Request $request, Closure $callback): array
    {
        $ttl = max((int) config('cache.api_response_ttl', 300), 1);
        $key = self::key($namespace, $request);

        $cached = Cache::get($key);

        if (is_array($cached)) {
            $request->attributes->set('api_response_cache_hit', true);

            return $cached;
        }

        $request->attributes->set('api_response_cache_hit', false);

        $payload = $callback();

        Cache::put($key, $payload, now()->addSeconds($ttl));

        return $payload;
    }

    public static function toResponse(Request $request, array $payload): JsonResponse
    {
        $response = response()->json($payload);

        $clientTtl = max((int) config('cache.api_client_cache_ttl', 60), 0);
        $staleWhileRevalidate = max((int) config('cache.api_stale_while_revalidate', 30), 0);
        $etag = sha1(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $response->setEtag($etag);
        $response->headers->set(
            'Cache-Control',
            sprintf('private, max-age=%d, stale-while-revalidate=%d', $clientTtl, $staleWhileRevalidate)
        );
        $response->headers->set('Vary', 'Accept, Authorization');
        $response->headers->set(
            'X-App-Cache',
            $request->attributes->get('api_response_cache_hit', false) ? 'HIT' : 'MISS'
        );

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }

    private static function key(string $namespace, Request $request): string
    {
        $routeParameters = collect($request->route()?->parametersWithoutNulls() ?? [])
            ->map(fn (mixed $value) => self::normalizeRouteValue($value))
            ->all();

        $payload = [
            'path' => $request->path(),
            'query' => Arr::sortRecursive($request->query()),
            'route' => $routeParameters,
        ];

        return sprintf(
            'api:%s:%s',
            $namespace,
            sha1(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
        );
    }

    private static function normalizeRouteValue(mixed $value): mixed
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'getRouteKey')) {
            return $value->getRouteKey();
        }

        return (string) $value;
    }
}