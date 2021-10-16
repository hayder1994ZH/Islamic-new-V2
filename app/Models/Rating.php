<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted','file_id'
    ];
    
    //files
	public function files()
	{
		return $this->belongsTo(Files::class);
    }
}
