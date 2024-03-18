<?php

namespace haseebmukhtar286\LaravelFormSdk\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use App\Traits\RoleTrait;
use App\Models\User;


class FormSubmission extends Model
{
    use SoftDeletes, RoleTrait;

    protected $fillable = [
        "project_id",
        "form_id",
        "data",
        "user_id",
        "schema_version",
        "report_no"
    ];

    protected $appends = ['report_slug'];

    function getReportSlugAttribute()
    {
        return env('REPORT_NUMBER_SLUG');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
