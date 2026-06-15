<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    |
    | When false, the inbound middleware and outbound helpers turn into
    | no-ops. Useful for opting out per-environment without touching code.
    |
    */
    'enabled' => env('ID_HASHING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Plaintext fallback
    |--------------------------------------------------------------------------
    |
    | While migrating, some pages may still emit raw numeric IDs. With this
    | flag on, the middleware leaves numeric values alone instead of
    | rejecting the request. Turn this OFF in production once every page is
    | switched over so a tampered URL with a guessable id reliably 404s.
    |
    */
    'allow_plaintext' => env('ID_HASHING_ALLOW_PLAINTEXT', env('APP_ENV') !== 'production'),

    /*
    |--------------------------------------------------------------------------
    | Route parameters that are auto-decrypted
    |--------------------------------------------------------------------------
    |
    | The middleware walks every named parameter on the matched route and
    | decrypts those whose name matches one of the regex patterns below.
    | Keep these focused on identifier-shaped names so opaque tokens
    | (`{token}`, `{slug}`, `{hash}`, …) flow through untouched.
    |
    */
    'param_patterns' => [
        '/^id$/',          // {id}
        '/^.+_id$/',       // {user_id}, {parent_id}
        '/^.+Id$/',        // {userId}, {cmsPageId}
    ],

    /*
    |--------------------------------------------------------------------------
    | Request input keys that are auto-decrypted
    |--------------------------------------------------------------------------
    |
    | Any POST/GET input whose key matches a regex below is decrypted
    | in-place before the request reaches the controller / FormRequest
    | validators. Mirror the same shape as `param_patterns` so URL
    | parameters and form fields behave identically.
    |
    */
    'input_patterns' => [
        '/^id$/',
        '/^.+_id$/',
        '/^.+Id$/',
        '/^ignore_id$/',   // CMS slug uniqueness check
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes / paths excluded from inbound decryption
    |--------------------------------------------------------------------------
    |
    | Path patterns (route paths or URI globs) where the middleware should
    | bail out entirely. Useful for third-party webhooks, raw-token routes
    | that already use random strings, etc.
    |
    */
    'except' => [
        'api/*',                       // public API endpoints
        'admin/password/reset/*',      // signed/random reset tokens
    ],

    /*
    |--------------------------------------------------------------------------
    | What happens on a bad cipher
    |--------------------------------------------------------------------------
    |
    | 'abort' — return a 404 immediately (default; matches Laravel's
    |           model-binding behaviour for unknown ids)
    | 'null'  — replace the value with null and let the controller decide
    |
    */
    'on_decode_failure' => env('ID_HASHING_ON_FAILURE', 'abort'),
];
