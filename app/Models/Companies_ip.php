<?php

namespace App\Models;

use App\Models\Companies;
use Illuminate\Database\Eloquent\Model;

class Companies_ip extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'is_deleted'
    ];

    public function company()
    {
        return $this->belongsTo(Companies::class);
    }

}
