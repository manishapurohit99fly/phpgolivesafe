<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'interval',
        'stripe_product_id',
        'stripe_price_id',
        'trial_days',
        'is_popular',
        'status',
        'button_text',
        'badge_text',
        'theme_color',
        'sort_order',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'is_popular' => 'boolean',
        'status'     => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
