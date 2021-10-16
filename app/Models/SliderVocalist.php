<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class SliderVocalist extends Model
{
    protected $guarded =[];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->addHour(0)->format('Y-m-d h:i:s');
    }

    public function vocalist()
    {
        return $this->belongsTo(Vocalist::class, 'vocalist_id');
    }
}
