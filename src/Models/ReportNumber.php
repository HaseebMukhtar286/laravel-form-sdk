<?php

namespace haseebmukhtar286\LaravelFormSdk\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use App\Traits\RoleTrait;
use App\Models\User;


class ReportNumber extends Model
{
    use SoftDeletes, RoleTrait;

    protected $fillable = [
        "reportCount",
        "status",
    ];
}
