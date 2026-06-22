<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistCategory extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.checklist_categories');
    }

    protected $fillable = [
        'tech_stack_id',
        'category_name',
        'sort_order',
    ];

    public function techStack(): BelongsTo
    {
        return $this->belongsTo(TechStack::class, 'tech_stack_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'category_id')->orderBy('sort_order');
    }
}
