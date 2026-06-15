<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.cms_pages');
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'og_image',
        'status',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function sections(){
        return $this->morphMany(
            Section::class,
            'content'
        );
    }
    
}
