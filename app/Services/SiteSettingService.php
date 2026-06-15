<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

final class SiteSettingService
{
    /**
     * Map of "setting_key" → "destination folder under public/".
     *
     * The `local` disk is rooted at public_path() (config/filesystems.php),
     * so the stored path can be fed straight into `asset()` from blade.
     * Adding a new image-typed setting only requires appending a row here
     * plus the matching validation rule in the controller.
     */
    private const FILE_FIELDS = [
        'site_logo'        => 'uploads/site_logos',
        'auth_side_banner' => 'uploads/auth_banners',
        'web_site_logo'    => 'uploads/web_logos',
        'web_favicon'      => 'uploads/web_favicons',
    ];

    /**
     * Load every site_settings row as a single fluent object so views can
     * keep using `$setting->some_key` access. Fluent returns null for
     * missing properties (no PHP notice), which keeps the existing
     * `?? 'fallback'` and `!empty(...)` checks working as-is.
     */
    public function getSettings(): Fluent
    {
        return new Fluent($this->loadRaw());
    }

    /**
     * Persist site-setting values, uploading any provided files and
     * deleting the previous file from disk when a new one replaces it.
     *
     * @param  array<string, mixed>              $values  scalar key → value
     * @param  array<string, UploadedFile|null>  $files   keyed by setting_key
     */
    public function updateSettings(array $values, array $files = []): void
    {
        $existing = $this->loadRaw();

        foreach ($files as $key => $file) {
            if (! $file instanceof UploadedFile) {
                unset($values[$key]);
                continue;
            }
            if (! array_key_exists($key, self::FILE_FIELDS)) {
                continue;
            }

            $previous = $existing[$key] ?? null;
            if ($previous && Storage::disk('local')->exists($previous)) {
                Storage::disk('local')->delete($previous);
            }

            // Preserve the actual uploaded extension (png stays png, svg
            // stays svg, etc.) instead of letting Laravel's hashName()
            // derive it from the MIME guess, which can collapse multiple
            // image types to ".jpg".
            $values[$key] = Storage::disk('local')->putFileAs(
                self::FILE_FIELDS[$key],
                $file,
                Str::random(40).'.'.$this->resolveExtension($file),
            );
        }

        $table = config('tables.site_settings');
        foreach ($values as $key => $value) {
            DB::table($table)->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => is_null($value) ? null : (string) $value],
            );
        }
    }

    /**
     * Pick the most trustworthy extension for an uploaded file.
     *
     * Priority:
     *   1. Original client extension (what the user actually uploaded —
     *      e.g. "png", "svg", "webp", "ico").
     *   2. MIME-based guess from the temp file (covers browsers that
     *      strip the extension before upload).
     *   3. Safe fallback of "png" so we never write an extension-less
     *      file to disk.
     */
    private function resolveExtension(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if ($extension === '') {
            $extension = strtolower((string) $file->guessExtension());
        }

        return $extension !== '' ? $extension : 'png';
    }

    /**
     * Flatten every row of site_settings into a single key → value map.
     *
     * Historic seeder data stored the literal string `'NULL'` for empty
     * fields, which leaks into blade as `value="NULL"` and trips the
     * browser's `type="url"` constraint validator on submit. Normalise
     * those (plus the lower-case variant) back to a real PHP null at
     * the read boundary so views always see a clean empty value.
     *
     * @return array<string, string|null>
     */
    private function loadRaw(): array
    {
        return DB::table(config('tables.site_settings'))
            ->pluck('setting_value', 'setting_key')
            ->map(fn ($value) => in_array($value, ['NULL', 'null'], true) ? null : $value)
            ->all();
    }
}
