<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasStatus;
use App\Traits\HasPostType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use SoftDeletes, HasSlug, HasStatus, HasPostType;

    protected $fillable = [
        'post_type',
        'status',
        'author_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'url',
        'parent_id',
        'menu_order',
        'published_at',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'og_image',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'menu_order'   => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.posts');
    }

    /**
     * WordPress-style image size buckets for the featured image.
     * Stored as files inside public/uploads/posts/{size}/{filename}.
     */
    public const FEATURED_IMAGE_SIZES = ['full', 'large', 'medium', 'thumbnail'];

    public const FEATURED_IMAGE_DIR = 'uploads/posts';

    // ─────────────────────────────────────────────
    // Featured image URL accessors (WP-like sizes)
    // ─────────────────────────────────────────────

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->featuredImageUrl('thumbnail');
    }

    public function getMediumUrlAttribute(): ?string
    {
        return $this->featuredImageUrl('medium');
    }

    public function getLargeUrlAttribute(): ?string
    {
        return $this->featuredImageUrl('large');
    }

    public function getFullUrlAttribute(): ?string
    {
        return $this->featuredImageUrl('full');
    }

    /**
     * Build a public asset URL for a given featured-image size.
     * Returns null when no featured image is set.
     */
    public function featuredImageUrl(string $size = 'full'): ?string
    {
        if (empty($this->featured_image)) {
            return null;
        }

        if (! in_array($size, self::FEATURED_IMAGE_SIZES, true)) {
            $size = 'full';
        }

        return asset(self::FEATURED_IMAGE_DIR . '/' . $size . '/' . $this->featured_image);
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_post')
                    ->withTimestamps()
                    ->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_post')
                    ->wherePivot('category_id', function ($q) {
                        $q->select('id')->from('categories')->where('type', 'tag');
                    })
                    ->withTimestamps();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id')->orderBy('menu_order');
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('menu_order')->orderBy('created_at', 'desc');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%")
              ->orWhere('excerpt', 'like', "%{$term}%");
        });
    }

    public function scopeInCategory(Builder $query, int|array $categoryId): Builder
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            is_array($categoryId)
                ? $q->whereIn('categories.id', $categoryId)
                : $q->where('categories.id', $categoryId);
        });
    }

    public function comments() {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->where('status', 'approved')->latest();
    }

    public function meta(){
        return $this->hasMany(PostMeta::class, 'post_id');
    }
    public function getMetaValue($key, $default = null) {
        $meta = $this->meta()->where('meta_key', $key)->first();
        return $meta ? $meta->meta_value : $default;
    }
}