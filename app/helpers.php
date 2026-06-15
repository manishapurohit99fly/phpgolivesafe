<?php
use App\Models\Category;
use App\Models\Menu;
use App\Models\Post;
use Illuminate\Support\Collection;


if (!function_exists('pre')) {
    function pre($data = '', $status = FALSE)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if (!$status) {
            die;
        }
    }
}

// For API
if (!function_exists('pree')) {
    function pree($data = '', $status = FALSE)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        if (!$status) {
            die;
        }
    }
}

if (!function_exists('getDateInFormat')) {
    function getDateInFormat($date)
    {
        if (!empty($date)) {
            $dateTimeObject = new DateTime($date);
            return $formattedDateTime = $dateTimeObject->format('d M, Y');
        } else {
            return '-';
        }
    }
}


if (!function_exists('get_avatar')) {
    function get_avatar($avatar = '')
    {
        return $avatar == '' ? asset('assets/img/user.png') : asset($avatar);
    }
}

if (!function_exists('encrypt_id')) {
    /**
     * Encrypt a single scalar id into a URL-safe ciphertext.
     *
     * Pairs with the `DecryptRouteIds` middleware which transparently
     * reverses this on the way in.
     */
    function encrypt_id($value)
    {
        return \App\Support\IdHasher::encode($value);
    }
}

if (!function_exists('decrypt_id')) {
    /**
     * Decrypt a URL-safe ciphertext back to the original id, or return
     * null if the value cannot be decoded. Intended for the rare case a
     * controller needs to handle a raw query string id manually — most
     * code should rely on the inbound middleware instead.
     */
    function decrypt_id($value)
    {
        return \App\Support\IdHasher::tryDecode($value);
    }
}

if (!function_exists('enroute')) {
    /**
     * Drop-in replacement for Laravel's `route()` that auto-encrypts any
     * scalar parameter (numeric ids, model keys, …) before generating the
     * URL.
     *
     * Mirrors the signature of `route()` so call sites read identically:
     *
     *   PHP        :  enroute('admin.userEdit', $user->id)
     *   Blade      :  href="{{ enroute('admin.userEdit', $user->id) }}"
     *   Controller :  return redirect()->to(enroute('admin.userEdit', $id));
     *
     * Behaviour:
     *  - Eloquent models resolve to their route key, then encrypt.
     *  - Arrays are walked one level deep so positional and named param
     *    arrays both work — `enroute('foo.bar', ['userId' => $id, …])`.
     *  - Already-base64url-shaped strings are left alone (no double encrypt).
     *
     * The companion to this is the `DecryptRouteIds` middleware which
     * reverses the encoding before the request reaches the controller, so
     * `$request->id` and `Route::current()->parameter('id')` always yield
     * the original numeric value.
     */
    function enroute(string $name, $parameters = [], bool $absolute = true): string
    {
        if ($parameters instanceof \UnitEnum) {
            $parameters = [$parameters];
        } elseif (! is_array($parameters)) {
            $parameters = [$parameters];
        }

        foreach ($parameters as $key => $value) {
            if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                $value = $value->getRouteKey();
            }

            $parameters[$key] = \App\Support\IdHasher::encode($value);
        }

        return route($name, $parameters, $absolute);
    }
}

if (!function_exists('eroute')) {
    /**
     * Backwards-compat alias for {@see enroute()}. New code should use
     * `enroute()` — this thin wrapper exists so any old call sites keep
     * working without modification.
     */
    function eroute(string $name, $parameters = [], bool $absolute = true): string
    {
        return enroute($name, $parameters, $absolute);
    }
}

if (!function_exists('table_action_icons')) {
  /**
   * Wrap one or more table action buttons in a consistent container.
   */
  function table_action_icons(string $content): string
  {
    return '<div class="action-icons d-inline-flex align-items-center">' . $content . '</div>';
  }
}

if (!function_exists('table_action_edit')) {
  function table_action_edit(string $href, string $title = 'Edit'): string
  {
    return '<a href="' . e($href) . '" class="table-action-btn btn-edit" title="' . e($title) . '">'
      . '<i class="fa fa-pen" aria-hidden="true"></i></a>';
  }
}

if (!function_exists('table_action_view')) {
    function table_action_view(string $href, string $title = 'View'): string
    {
      return '<a target="_blank" href="' . e($href) . '" class="table-action-btn btn-view" title="' . e($title) . '">'
        . '<i class="fa fa-eye" aria-hidden="true"></i></a>';
    }
}


if (!function_exists('table_action_delete')) {
  function table_action_delete(string $onclick, string $title = 'Delete'): string
  {
    return '<a href="javascript:void(0)" class="table-action-btn btn-delete" title="' . e($title) . '" onclick="' . $onclick . '">'
      . '<i class="fa fa-trash-can" aria-hidden="true"></i></a>';
  }
}


if (!function_exists('table_action_reset_password')) {
  function table_action_reset_password(string $encId, string $name, string $email): string
  {
    return '<a href="javascript:void(0)" title="Reset Password" class="table-action-btn btn-view reset-password-btn"'
      . ' data-id="' . e($encId) . '"'
      . ' data-name="' . htmlspecialchars($name, ENT_QUOTES) . '"'
      . ' data-email="' . htmlspecialchars($email, ENT_QUOTES) . '">'
      . '<i class="fa fa-key" aria-hidden="true"></i></a>';
  }
}

if (!function_exists('encryptParams')) {
    /**
     * Backwards-compat wrapper around the new ID hasher. Existing code
     * that depended on the URL-unsafe cipher emitted by `Crypt` should
     * migrate to `encrypt_id()`/`enroute()`, but this keeps working in
     * the meantime.
     */
    function encryptParams(...$args)
    {
        return array_map(function ($arg) {
            return \App\Support\IdHasher::encode($arg);
        }, $args);
    }
}


if (! function_exists('getMenu')) {
    function getMenu(string $key): Collection
    {   
        $menu = Menu::with('items')->where('slug', $key)->first();
        return $menu ? $menu->items : collect();
    }
}

if (! function_exists('getPostsByType')) {
    /**
     * Get published posts by post_type with eager-loaded author & categories.
     */
    function getPostsByType(string $type, int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Post::byType($type)
            ->published()
            ->with(['author', 'categories'])
            ->ordered()
            ->paginate($perPage);
    }
}
 
if (! function_exists('getPostBySlug')) {
    /**
     * Find a published post by slug (any type). Returns null if not found.
     */
    function getPostBySlug(string $slug, ?string $type = null): ?Post
    {
        $query = Post::published()->where('slug', $slug)->with(['author', 'categories', 'parent']);
 
        if ($type) {
            $query->byType($type);
        }
 
        return $query->firstOrFail();
    }
}
 
    if (! function_exists('getCategoriesByType')) {
        /**
         * Get categories of a given type, optionally with post counts.
         */
        function getCategoriesByType(string $type, bool $withCounts = false): Collection
        {
            $query = Category::byType($type)->ordered()->topLevel();
    
            if ($withCounts) {
                $query->withCount(['posts' => fn ($q) => $q->published()]);
            }
    
            return $query->with('children')->get();
        }
    }
 
    if (! function_exists('renderMenuTree')) {
        function renderMenuTree(Collection $items, string $ulClass = 'navbar-nav mx-auto', string $childClass = 'dropdown-menu'): string
        {
            if ($items->isEmpty()) {
                return '';
            }

            $html = "<ul class=\"{$ulClass}\">";

            foreach ($items as $item) {

                $hasChildren = $item->children && $item->children->isNotEmpty();

                $liClass = $hasChildren ? 'nav-item dropdown position-relative' : 'nav-item';
                $aClass  = $hasChildren ? 'nav-link dropdown-toggle' : 'nav-link';

                if (! empty($item->css_class)) {
                    $liClass .= ' ' . $item->css_class;
                }

                $href   = $item->url ?: '#';
                $target = $item->target ?: '_self';

                $html .= "<li class=\"{$liClass}\">";

                $html .= "<a class=\"{$aClass}\" href=\"" . e($href) . '"';
                $html .= ' target="' . e($target) . '"';

                // REMOVE bootstrap click dropdown
                // if ($hasChildren) {
                //     $html .= ' data-bs-toggle="dropdown" aria-expanded="false"';
                // }

                $html .= '>';

                if (! empty($item->icon)) {
                    $html .= '<i class="' . e($item->icon) . '"></i> ';
                }

                $html .= e($item->title) . '</a>';

                if ($hasChildren) {
                    $html .= renderMenuTree(
                        $item->children,
                        $childClass,
                        'dropdown-menu dropdown-submenu'
                    );
                }

                $html .= '</li>';
            }

            $html .= '</ul>';

            return $html;
        }
    }
 
    if (! function_exists('clearMenuCache')) {
        /**
         * Flush every cached menu (call after creating / updating / deleting menus
         * or menu items). Iterates over all menus so any custom `location` or
         * `slug` keys are invalidated, not just `header` / `footer`.
         */
        function clearMenuCache(): void
        {
            cache()->forget('menu:header');
            cache()->forget('menu:footer');

            Menu::query()
                ->select(['location', 'slug'])
                ->get()
                ->each(function (Menu $menu) {
                    if (! empty($menu->location)) {
                        cache()->forget('menu:' . $menu->location);
                    }
                    if (! empty($menu->slug)) {
                        cache()->forget('menu:' . $menu->slug);
                    }
                });
        }
    }