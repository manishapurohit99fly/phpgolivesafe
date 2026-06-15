<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{   
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.sections');
    }
    protected $fillable = [
        'content_type',
        'content_id',
        'section_template_id',
        'key',
        'content',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'content' => 'array',
        'status'  => 'boolean',
    ];
    

    public function content(){
        return $this->morphTo();
    }

    public function template()
    {
        return $this->belongsTo(
            SectionTemplate::class,
            'section_template_id'
        );
    }
}