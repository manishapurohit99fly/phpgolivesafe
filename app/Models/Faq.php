<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.faqs');
    }

    protected $fillable = [
        'question',
        'answer',
        'status',        
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    
}
