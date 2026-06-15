<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'parent_id',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'category_post')->withTimestamps();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
                    ->orderBy('sort_order');
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeBlogCategories(Builder $query): Builder
    {
        return $query->where('type', 'blog_category');
    }

    public function scopeNavMenus(Builder $query): Builder
    {
        return $query->where('type', 'nav_menu');
    }

    public function scopeFooterMenus(Builder $query): Builder
    {
        return $query->where('type', 'footer_menu');
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return match ($this->type) {
            'blog_category' => route('blog.category', $this->slug),
            default         => '#',
        };
    }

    public function getPostCountAttribute(): int
    {
        return $this->posts()->published()->count();
    }

    /**
     * Recursively load all descendants.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }
}