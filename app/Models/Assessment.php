<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.assessments');
    }

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status',
        'created_by',
        'submitted_at',
    ];

    protected $casts = [
        'status'       => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(AssessmentChecklist::class, 'assessment_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('tables.assessment_users'),
            'assessment_id',
            'user_id'
        );
    }

    public function sharedReports(): HasMany
    {
        return $this->hasMany(SharedReport::class, 'assessment_id');
    }
}
