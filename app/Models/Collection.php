<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ]; 
    		 
    public function files()
    {
		return $this->hasMany(Files::class, 'collection_id')->whereHas('object',function ($objectModel) {
            $objectModel->where('type_audio', 1)->orWhere('type_audio', 2);
     })->where('is_deleted', 0);
    }
    
    //user
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
    }
}
