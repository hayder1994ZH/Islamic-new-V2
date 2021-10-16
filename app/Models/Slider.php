<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $guarded =[];

    public function file()
    {
        return $this->belongsTo(Files::class, 'file_id');
    }
}
