<?php

namespace App\Models;

use App\Companies_ip;
use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'is_deleted'
    ];

    public function ips()
    {
        return $this->hasMany(Companies_ip::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
