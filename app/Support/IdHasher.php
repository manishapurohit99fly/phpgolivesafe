<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Single source of truth for encrypting/decrypting IDs that travel through
 * URLs, request bodies and HTML data attributes.
 *
 * Why a wrapper around Crypt?
 *  1. URL-safety — Crypt::encryptString() emits standard base64 (`+`, `/`,
 *     `=`) which has to be percent-encoded inside URLs. We base64url-encode
 *     the cipher (`+`→`-`, `/`→`_`, strip `=`) so an encrypted ID can sit
 *     verbatim in a path segment, query string or data-attribute.
 *  2. Symmetry — keeps encrypt/decrypt rules in one place so the inbound
 *     middleware and the outbound helpers can never drift apart.
 *  3. Soft-fail decode — `tryDecode()` returns null instead of throwing so
 *     the middleware can decide whether to 404 or fall through to the
 *     plaintext compatibility path.
 *
 * The plaintext fallback exists purely so a partially-migrated codebase
 * keeps working while pages are converted; production should keep
 * `id_hashing.allow_plaintext` set to false.
 */
final class IdHasher
{
    /**
     * Encrypt a scalar ID into a URL-safe ciphertext. Non-scalar / null
     * values are returned unchanged so callers can pipe arbitrary route
     * parameter arrays through this method without filtering first.
     */
    public static function encode(mixed $value): mixed
    {
        if ($value === null || $value === '' || is_array($value) || is_object($value)) {
            return $value;
        }

        // Already encoded — don't double-encrypt. base64url ciphers contain
        // only `A-Za-z0-9-_` and are noticeably longer than any DB id we'd
        // realistically pass in.
        if (is_string($value) && self::looksEncoded($value)) {
            return $value;
        }

        $cipher = Crypt::encryptString((string) $value);

        return self::base64UrlEncode($cipher);
    }

    /**
     * Decrypt a URL-safe ciphertext back to the original string.
     *
     * Returns null if `$value` is not a valid cipher. Numeric / empty inputs
     * are passed through untouched when `allow_plaintext` is enabled, which
     * lets controllers consume the parameter exactly the same way whether
     * the request came from a freshly-migrated page or a legacy URL.
     */
    public static function tryDecode(mixed $value): mixed
    {
        if ($value === null || $value === '' || is_array($value) || is_object($value)) {
            return $value;
        }

        $allowPlaintext = (bool) config('id_hashing.allow_plaintext', false);

        if ($allowPlaintext && is_numeric($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return $allowPlaintext ? $value : null;
        }

        $cipher = self::base64UrlDecode($value);

        try {
            return Crypt::decryptString($cipher);
        } catch (DecryptException $e) {
            return $allowPlaintext && is_numeric($value) ? $value : null;
        }
    }

    /**
     * Strict variant that throws when the input cannot be decoded. Used by
     * routes/middleware that want to fail loudly instead of silently
     * dropping a parameter.
     */
    public static function decode(mixed $value): mixed
    {
        $decoded = self::tryDecode($value);

        if ($decoded === null && $value !== null && $value !== '') {
            throw new DecryptException('Invalid encrypted identifier.');
        }

        return $decoded;
    }

    /**
     * Cheap heuristic used to skip already-encoded values. base64url
     * alphabet only — anything else (e.g. a digit) is treated as raw.
     */
    public static function looksEncoded(string $value): bool
    {
        // Crypt ciphertexts are JSON envelopes with iv/value/mac, so even
        // the shortest realistic payload base64-encodes to ~100+ chars.
        // Use 40 as a safety floor that's still well above any DB id.
        if (strlen($value) < 40) {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z0-9_-]+$/', $value);
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        $padded = str_pad($value, strlen($value) + ((4 - (strlen($value) % 4)) % 4), '=', STR_PAD_RIGHT);

        return base64_decode(strtr($padded, '-_', '+/'), true) ?: '';
    }
}
