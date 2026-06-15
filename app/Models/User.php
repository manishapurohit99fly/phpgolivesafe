<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Common_trait;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, Common_trait;

    // Explicitly define the table name
    protected $table;
    
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'password',        
        'device_type',
        'device_id',
        'created_at',
        'updated_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.users');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function assignedProjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Project::class,
            config('tables.project_users'),
            'user_id',
            'project_id'
        );
    }
}
