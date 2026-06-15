<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\IdHasher;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Centralised inbound-decryption layer.
 *
 * For every request that flows through the `web` middleware group, this
 * filter:
 *   1. Walks the matched route's named parameters and decrypts any whose
 *      name looks like an identifier (`id`, `user_id`, `userId`, …).
 *   2. Walks the request's POST/GET input bag and decrypts the same set of
 *      keys, so `$request->id`, `$request->user_id` etc. land in the
 *      controller as plain numeric values.
 *
 * After this middleware, no controller, FormRequest or Blade view in the
 * pipeline needs to know that the incoming value was ever encrypted.
 *
 * Pattern lists, the plaintext fallback and the failure mode are all
 * driven by `config/id_hashing.php` so the policy lives in one file.
 */
class DecryptRouteIds
{
    /**
     * Cached compiled patterns so `preg_match()` only fires once per
     * config entry per request.
     *
     * @var array<int, string>
     */
    private array $paramPatterns = [];

    /**
     * @var array<int, string>
     */
    private array $inputPatterns = [];

    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('id_hashing.enabled', true)) {
            return $next($request);
        }

        if ($this->isExcluded($request)) {
            return $next($request);
        }

        $this->paramPatterns = (array) config('id_hashing.param_patterns', []);
        $this->inputPatterns = (array) config('id_hashing.input_patterns', []);

        $this->decryptRouteParameters($request);
        $this->decryptRequestInputs($request);

        return $next($request);
    }

    /**
     * Walk the matched route's parameters and replace any identifier-
     * shaped value with its decrypted form.
     *
     * Runs before the controller method is invoked and before any model
     * binding fires, so `Route::bind()` callbacks receive the plain id.
     */
    private function decryptRouteParameters(Request $request): void
    {
        $route = $request->route();
        if (! $route) {
            return;
        }

        foreach ($route->parameters() as $name => $value) {
            if (! $this->matches($name, $this->paramPatterns)) {
                continue;
            }

            // Sub-resource binding may already have produced a model — skip.
            if (! is_scalar($value)) {
                continue;
            }

            $decoded = IdHasher::tryDecode($value);

            if ($decoded === null) {
                $this->handleFailure($name, $value);
                continue;
            }

            $route->setParameter($name, $decoded);
        }
    }

    /**
     * Decrypt matching keys inside the request input bag.
     *
     * `replace()` mutates the bag in-place so subsequent `$request->id`,
     * `Request::input('user_id')` and FormRequest validation all see the
     * decoded value.
     */
    private function decryptRequestInputs(Request $request): void
    {
        $all = $request->all();
        if (empty($all)) {
            return;
        }

        $changed = false;

        foreach ($all as $key => $value) {
            if (! $this->matches((string) $key, $this->inputPatterns)) {
                continue;
            }

            // Arrays (e.g. multi-select delete: ids[]=…) — decrypt each leaf.
            if (is_array($value)) {
                $decodedArray = $this->decryptArray($value);
                if ($decodedArray !== $value) {
                    $all[$key] = $decodedArray;
                    $changed   = true;
                }
                continue;
            }

            if (! is_scalar($value)) {
                continue;
            }

            $decoded = IdHasher::tryDecode($value);

            if ($decoded === null) {
                $this->handleFailure($key, $value);
                continue;
            }

            if ((string) $decoded !== (string) $value) {
                $all[$key] = $decoded;
                $changed   = true;
            }
        }

        if ($changed) {
            $request->replace($all);
        }
    }

    /**
     * @param  array<mixed>  $values
     * @return array<mixed>
     */
    private function decryptArray(array $values): array
    {
        foreach ($values as $i => $v) {
            if (is_array($v)) {
                $values[$i] = $this->decryptArray($v);
                continue;
            }

            if (! is_scalar($v)) {
                continue;
            }

            $decoded = IdHasher::tryDecode($v);

            if ($decoded === null) {
                continue; // skip silently inside arrays — caller validates
            }

            $values[$i] = $decoded;
        }

        return $values;
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function matches(string $name, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, $name) === 1) {
                return true;
            }
        }

        return false;
    }

    private function isExcluded(Request $request): bool
    {
        foreach ((array) config('id_hashing.except', []) as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * What to do when a value can't be decrypted — drives the entire
     * tampering story so it lives in one place.
     */
    private function handleFailure(string $key, mixed $value): void
    {
        $mode = (string) config('id_hashing.on_decode_failure', 'abort');

        if ($mode === 'abort') {
            // 404 keeps tampered URLs visually indistinguishable from
            // genuinely missing rows — this is what we want.
            abort(404);
        }

        // 'null' mode: nothing to do, the value will simply not match any
        // record on lookup.
    }
}
