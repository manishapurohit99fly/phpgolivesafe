<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPostType
{
    /**
     * Scope by a specific post type or array of types.
     */
    public function scopeByType(Builder $query, string|array $type): Builder
    {
        return is_array($type)
            ? $query->whereIn('post_type', $type)
            : $query->where('post_type', $type);
    }

    /**
     * Scope: blog posts only.
     */
    public function scopePosts(Builder $query): Builder
    {
        return $query->where('post_type', 'post');
    }


    /**
     * Scope: header menu items only.
     */
    public function scopeMenuItems(Builder $query): Builder
    {
        return $query->where('post_type', 'menu_item');
    }

    /**
     * Scope: footer menu items only.
     */
    public function scopeFooterItems(Builder $query): Builder
    {
        return $query->where('post_type', 'footer_item');
    }

    /**
     * Check if this record matches a given post type.
     */
    public function isType(string $type): bool
    {
        return $this->post_type === $type;
    }
}