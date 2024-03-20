<?php

namespace haseebmukhtar286\LaravelFormSdk\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use App\Traits\RoleTrait;
use App\Models\User;


class FormSubmissionHistory extends Model
{
    use SoftDeletes, RoleTrait;

    protected $fillable = [
        "form_id",
        "reason",
        "user_id",
        "status",
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
