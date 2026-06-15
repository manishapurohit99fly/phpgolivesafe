<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the trait and auto-generate slug on creating.
     */
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && $model->isDirty('slug') === false) {
                $model->slug = $model->generateUniqueSlug($model->title);
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    public function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists in the table.
     */
    protected function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = static::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * Regenerate slug from current title.
     */
    public function regenerateSlug(): void
    {
        $this->slug = $this->generateUniqueSlug($this->title, $this->id);
        $this->save();
    }
}