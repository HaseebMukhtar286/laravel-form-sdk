<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

use App\Traits\RoleTrait;

class ObligationSites extends Model
{

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function cluster()
    {
        return $this->belongsTo(Cluster::class, 'cluster_id');
    }

}
