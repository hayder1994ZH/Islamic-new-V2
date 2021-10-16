<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temp_files extends Model
{
    
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted',
    ];
    
    //files
	public function files()
	{
		return $this->belongsTo(Files::class);
    }
}
