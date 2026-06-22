<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.projects');
    }

    protected $fillable = [
        'project_name',
        'project_description',
        'client_name',
        'project_url',
        'tech_stack_id',
        'deployment_notes',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function techStack(): BelongsTo
    {
        return $this->belongsTo(TechStack::class, 'tech_stack_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(ProjectChecklist::class, 'project_id');
    }

    public function sharedReports(): HasMany
    {
        return $this->hasMany(SharedReport::class, 'project_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'project_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('tables.project_users'),
            'project_id',
            'user_id'
        );
    }
}
