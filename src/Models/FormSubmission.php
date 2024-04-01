<?php

namespace haseebmukhtar286\LaravelFormSdk\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use App\Traits\RoleTrait;
use App\Models\User;
use App\Models\ObligationSites;


class FormSubmission extends Model
{
    use SoftDeletes, RoleTrait;

    protected $fillable = [
        "project_id",
        "form_id",
        "data",
        "user_id",
        "schema_version",
        "report_no",
        "status"
    ];

    protected $appends = ['report_slug'];

    function getReportSlugAttribute()
    {
        return env('REPORT_NUMBER_SLUG'). $this->report_no;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function site()
    {
        return $this->belongsTo(ObligationSites::class, 'data.site.key');
    }
}
