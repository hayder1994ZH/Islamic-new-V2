<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Tags_files extends Model
{
    
    protected $guarded =[];


    public function files()
    {
        return $this->hasMany(Files::class, 'id', 'file_id')->where('is_deleted', 0);
    }
}
