<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vocalist extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ]; 
    
    //user
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
    }

    //user
	public function files()
	{
		return $this->hasMany(Files::class, 'vocalist_id');
    }
}
