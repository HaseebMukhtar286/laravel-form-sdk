<?php

namespace haseebmukhtar286\LaravelFormSdk\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;


class FormSchema extends Model
{
    use SoftDeletes;
    protected $fillable = [
        // "name",
        // "projectId",
        // "icon",
        // "status",
        // "schema",
        // "slug",
        "form_id",
        "data",
        "schema_version"
    ];
}
