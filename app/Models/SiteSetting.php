<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.site_settings');
    }

    protected $fillable = [
        'site_name',
        'site_logo',
        'auth_side_banner',
        'admin_theme_colour',
        'admin_secondary_colour',
        'two_factor_enabled',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'twitter_url',
    ];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
    ];
}
