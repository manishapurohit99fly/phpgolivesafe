<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'category_name',
        'sort_order',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'category_id')->orderBy('sort_order');
    }
}
