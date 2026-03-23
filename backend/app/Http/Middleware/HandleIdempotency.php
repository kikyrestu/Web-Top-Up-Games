<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\IdempotencyRequest;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

final class HandleIdempotency
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->supportsMethod($request)) {
            return $next($request);
        }

        $idempotencyKey = $this->resolveKey($request);
        if ($idempotencyKey === null) {
            return $next($request);
        }

        $scope = $this->resolveScope($request);
        $actorFingerprint = $this->resolveActorFingerprint($request);
        $requestHash = $this->requestHash($request);

        $existing = IdempotencyRequest::query()
            ->where('scope', $scope)
            ->where('idempotency_key', $idempotencyKey)
            ->where('actor_fingerprint', $actorFingerprint)
            ->first();

        if ($existing !== null) {
            if (!hash_equals((string) $existing->request_hash, $requestHash)) {
                return response()->json([
                    'success' => false,
                    'code' => 'IDEMPOTENCY_KEY_REUSED_WITH_DIFFERENT_PAYLOAD',
                    'message' => 'Idempotency key already used with different request payload',
                    'data' => null,
                ], 409);
            }

            return response()->json(
                is_array($existing->response_body) ? $existing->response_body : [],
                (int) $existing->response_status
            );
        }

        $response = $next($request);

        if (!$response instanceof JsonResponse) {
            return $response;
        }

        if ($response->getStatusCode() >= 500) {
            return $response;
        }

        IdempotencyRequest::query()->create([
            'scope' => $scope,
            'idempotency_key' => $idempotencyKey,
            'actor_fingerprint' => $actorFingerprint,
            'request_hash' => $requestHash,
            'response_status' => $response->getStatusCode(),
            'response_body' => Arr::wrap($response->getData(true)),
            'expires_at' => now()->addHours(max(1, (int) config('services.idempotency.ttl_hours', 24))),
        ]);

        return $response;
    }

    private function supportsMethod(Request $request): bool
    {
        return in_array(strtoupper($request->getMethod()), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function resolveKey(Request $request): ?string
    {
        $header = trim((string) $request->header('x-idempotency-key', ''));
        if ($header !== '') {
            return substr($header, 0, 120);
        }

        $bodyKey = trim((string) $request->input('idempotency_key', ''));
        if ($bodyKey !== '') {
            return substr($bodyKey, 0, 120);
        }

        return null;
    }

    private function resolveScope(Request $request): string
    {
        $method = strtoupper($request->getMethod());
        $route = $request->route();
        $uri = is_object($route) && method_exists($route, 'uri')
            ? (string) $route->uri()
            : (string) $request->path();

        return $method.':'.$uri;
    }

    private function resolveActorFingerprint(Request $request): string
    {
        $userId = $request->user()?->id;

        if ($userId !== null) {
            return 'user:'.(string) $userId;
        }

        return 'guest:'.sha1((string) $request->ip());
    }

    private function requestHash(Request $request): string
    {
        $payload = $request->all();
        ksort($payload);

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return hash('sha256', is_string($json) ? $json : '{}');
    }
}
