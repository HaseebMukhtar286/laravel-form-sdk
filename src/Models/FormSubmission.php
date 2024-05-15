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
        "report_no",
        "status",
        "is_data_compiled"
    ];

    protected $appends = ['inspection_type'];

    public function getInspectionTypeAttribute()
    {
        return  $this->user->type ?? null === "facility"? "Self assessment": "Inspection";
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
