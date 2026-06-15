<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\IdHasher;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Wires up the convenience layer that lives on top of {@see IdHasher}:
 *
 *  - merges the `id_hashing` config (so the package works without a
 *    separately published config file) and
 *  - registers the `@eid($value)` Blade directive for ergonomic use
 *    inside templates ({{-- e.g. data-id="@eid($user->id)" --}}).
 *
 * The inbound middleware and the helper functions (`encrypt_id`,
 * `decrypt_id`, `enroute`) are registered elsewhere — this provider only
 * owns the boot-time integrations with Laravel.
 */
class IdHashingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/id_hashing.php',
            'id_hashing'
        );
    }

    public function boot(): void
    {
        // `@eid($value)` — emits a URL-safe encrypted form of the value.
        // Use inside Blade `data-` attributes, hidden inputs, or anywhere
        // an id would otherwise leak in plain form.
        Blade::directive('eid', function (string $expression): string {
            return "<?php echo e(\\App\\Support\\IdHasher::encode({$expression})); ?>";
        });
    }
}
