<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplates extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.email_templates');
    }

    protected $fillable = [
        'name',
        'subject',
        'body',
        'status',
    ];

    /**
     * Look up an active template by its slug-shaped identifier.
     * The `name` column stores the slug (lowercase, hyphen-separated).
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->where('status', 1)->first();
    }
}
