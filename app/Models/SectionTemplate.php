<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionTemplate extends Model
{
    //
    use SoftDeletes;
    protected $table;
    protected $fillable = [
        'name',
        'key',
        'content_type',
        'fields',
        'status',
    ];
 
    protected $casts = [
        'fields' => 'array',
        'status' => 'boolean',
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.section_templates');
       
    }

}
