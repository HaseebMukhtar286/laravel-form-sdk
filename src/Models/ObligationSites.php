<?php

namespace haseebmukhtar286\LaravelFormSdk\Models;


use Jenssegers\Mongodb\Eloquent\Model;

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
