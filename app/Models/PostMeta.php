<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMeta extends Model
{

    protected $table;

    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
    ];
    public $timestamps = false;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.post_meta');
       
    }

    function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

}
