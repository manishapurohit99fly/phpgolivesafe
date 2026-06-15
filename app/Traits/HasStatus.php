<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasStatus
{
    /**
     * Scope: published records only.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    /**
     * Scope: draft records only.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if the record is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && ($this->published_at === null || $this->published_at->lte(now()));
    }

    /**
     * Check if the record is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Publish the record immediately.
     */
    public function publish(?Carbon $publishAt = null): void
    {
        $this->update([
            'status'       => 'published',
            'published_at' => $publishAt ?? now(),
        ]);
    }

    /**
     * Revert to draft.
     */
    public function unpublish(): void
    {
        $this->update(['status' => 'draft']);
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'Published',
            'draft'     => 'Draft',
            default     => ucfirst($this->status),
        };
    }

    /**
     * Get Bootstrap badge class for status.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft'     => 'secondary',
            default     => 'light',
        };
    }
}