<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.checklist_items');
    }

    protected $fillable = [
        'category_id',
        'checklist_item',
        'sort_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ChecklistCategory::class, 'category_id');
    }
}
