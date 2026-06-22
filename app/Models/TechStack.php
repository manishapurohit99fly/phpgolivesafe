<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechStack extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.tech_stacks');
    }

    protected $fillable = ['name', 'status', 'sort_order'];

    protected $casts = ['status' => 'integer'];

    public function categories(): HasMany
    {
        return $this->hasMany(ChecklistCategory::class, 'tech_stack_id')->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'tech_stack_id');
    }
}
