<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $table;
    protected $fillable = [
        'post_id',
        'user_id',
        'name',
        'email',
        'website',
        'comment',
        'comment_type',
        'parent_id',
        'status',
        'ip_address',
        'user_agent',
        'approved_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.comments');
       
    }


    /* Relations */
   
     public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->where('status', 'approved');
    }
}
