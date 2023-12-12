<?php

namespace haseebmukhtar286\LaravelFormSdk\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;


class FormSchema extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "schema",
        "name",
        "questions",
        "projectId",
        "icon",
    ];

    // public function project()
    // {
    //     return $this->belongsTo(Project::class, 'projectId');
    // }

    // public function getProjectNameAttribute()
    // {
    //     return $this->project->name ?? null;
    // }
}
