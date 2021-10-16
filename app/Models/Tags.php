<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted',
        'pivot'
    ];
    
    //files
	public function files()
	{
		return $this->belongsTo(Files::class)->where('is_deleted', 0);
    }
}
