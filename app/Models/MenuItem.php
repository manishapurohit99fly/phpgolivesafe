<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [

        'menu_id',
        'parent_id',
        'title',
        'url',
        'reference_type',
        'reference_id',
        'target',
        'menu_order',
        'css_class',
        'icon',
        'is_active',

    ];

     public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.menu_items');
       
    }
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('menu_order');
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Reference Resolver
    |--------------------------------------------------------------------------
    */

    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return match ($this->reference_type) {

            'cms_page' => CmsPage::find($this->reference_id),

            'post' => Post::find($this->reference_id),

            'category' => Category::find($this->reference_id),

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('menu_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}