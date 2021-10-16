<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ];
    
	public function files()
	{
		return $this->belongsTo(Files::class, 'file_id');
    }
}
